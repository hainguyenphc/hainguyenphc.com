<?php

declare(strict_types=1);

namespace Drupal\webprofiler\Csp;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Handles Content-Security-Policy HTTP header for the WebProfiler Bundle.
 *
 * @internal
 */
class ContentSecurityPolicyHandler {

  /**
   * TRUE if the Content-Security-Policy is disabled.
   *
   * @var bool
   */
  private bool $cspDisabled = FALSE;

  /**
   * ContentSecurityPolicyHandler constructor.
   *
   * @param \Drupal\webprofiler\Csp\NonceGenerator $nonceGenerator
   *   The nonce generator service.
   */
  public function __construct(
    protected readonly NonceGenerator $nonceGenerator,
  ) {
  }

  /**
   * Returns an array of nonces.
   *
   * To be used in Twig templates and Content-Security-Policy headers.
   *
   * Nonce can be provided by;
   *  - The request - In case HTML content is fetched via AJAX and inserted in
   * DOM, it must use the same nonce as origin.
   *  - The response - A call to getNonces() has already been done previously.
   * Same nonce are returned.
   *  - They are otherwise randomly generated.
   */
  public function getNonces(Request $request, Response $response): array {
    if ($request->headers->has('X-SymfonyProfiler-Script-Nonce') && $request->headers->has('X-SymfonyProfiler-Style-Nonce')) {
      return [
        'csp_script_nonce' => $request->headers->get('X-SymfonyProfiler-Script-Nonce'),
        'csp_style_nonce' => $request->headers->get('X-SymfonyProfiler-Style-Nonce'),
      ];
    }

    if ($response->headers->has('X-SymfonyProfiler-Script-Nonce') && $response->headers->has('X-SymfonyProfiler-Style-Nonce')) {
      return [
        'csp_script_nonce' => $response->headers->get('X-SymfonyProfiler-Script-Nonce'),
        'csp_style_nonce' => $response->headers->get('X-SymfonyProfiler-Style-Nonce'),
      ];
    }

    $nonces = [
      'csp_script_nonce' => $this->generateNonce(),
      'csp_style_nonce' => $this->generateNonce(),
    ];

    $response->headers->set('X-SymfonyProfiler-Script-Nonce', $nonces['csp_script_nonce']);
    $response->headers->set('X-SymfonyProfiler-Style-Nonce', $nonces['csp_style_nonce']);

    return $nonces;
  }

  /**
   * Disables Content-Security-Policy.
   *
   * All related headers will be removed.
   */
  public function disableCsp(): void {
    $this->cspDisabled = TRUE;
  }

  /**
   * Cleanup temporary headers and updates Content-Security-Policy headers.
   *
   * @return array
   *   Nonces used by the bundle in Content-Security-Policy header
   */
  public function updateResponseHeaders(Request $request, Response $response): array {
    if ($this->cspDisabled) {
      $this->removeCspHeaders($response);

      return [];
    }

    $nonces = $this->getNonces($request, $response);
    $this->cleanHeaders($response);
    $this->updateCspHeaders($response, $nonces);

    return $nonces;
  }

  /**
   * Clean headers.
   *
   * @param \Symfony\Component\HttpFoundation\Response $response
   *   The Response object.
   */
  private function cleanHeaders(Response $response): void {
    $response->headers->remove('X-SymfonyProfiler-Script-Nonce');
    $response->headers->remove('X-SymfonyProfiler-Style-Nonce');
  }

  /**
   * Remove CSP headers.
   *
   * @param \Symfony\Component\HttpFoundation\Response $response
   *   The Response object.
   */
  private function removeCspHeaders(Response $response): void {
    $response->headers->remove('X-Content-Security-Policy');
    $response->headers->remove('Content-Security-Policy');
    $response->headers->remove('Content-Security-Policy-Report-Only');
  }

  /**
   * Updates Content-Security-Policy headers in a response.
   */
  private function updateCspHeaders(Response $response, array $nonces = []): array {
    $nonces = \array_replace([
      'csp_script_nonce' => $this->generateNonce(),
      'csp_style_nonce' => $this->generateNonce(),
    ], $nonces);

    $ruleIsSet = FALSE;

    $headers = $this->getCspHeaders($response);

    $types = [
      'script-src' => 'csp_script_nonce',
      'script-src-elem' => 'csp_script_nonce',
      'style-src' => 'csp_style_nonce',
      'style-src-elem' => 'csp_style_nonce',
    ];

    foreach ($headers as $header => $directives) {
      foreach ($types as $type => $tokenName) {
        if ($this->authorizesInline($directives, $type)) {
          continue;
        }
        if (!isset($headers[$header][$type])) {
          if (NULL === $fallback = $this->getDirectiveFallback($directives, $type)) {
            continue;
          }

          if (['\'none\''] === $fallback) {
            // Fallback came from "default-src: 'none'"
            // 'none' is invalid if it's not the only expression in the source
            // list, so we leave it out.
            $fallback = [];
          }

          $headers[$header][$type] = $fallback;
        }
        $ruleIsSet = TRUE;
        if (!\in_array('\'unsafe-inline\'', $headers[$header][$type], TRUE)) {
          $headers[$header][$type][] = '\'unsafe-inline\'';
        }
        $headers[$header][$type][] = \sprintf('\'nonce-%s\'', $nonces[$tokenName]);
      }
    }

    if (!$ruleIsSet) {
      return $nonces;
    }

    foreach ($headers as $header => $directives) {
      $response->headers->set($header, $this->generateCspHeader($directives));
    }

    return $nonces;
  }

  /**
   * Generates a valid Content-Security-Policy nonce.
   */
  private function generateNonce(): string {
    return $this->nonceGenerator->generate();
  }

  /**
   * Converts a directive set array into Content-Security-Policy header.
   */
  private function generateCspHeader(array $directives): string {
    return \array_reduce(\array_keys($directives), static function ($res, $name) use ($directives) {
      return ('' !== $res ? $res . '; ' : '') . \sprintf('%s %s', $name, \implode(' ', $directives[$name]));
    }, '');
  }

  /**
   * Converts a Content-Security-Policy header value into a directive set array.
   */
  private function parseDirectives(string $header): array {
    $directives = [];

    foreach (\explode(';', $header) as $directive) {
      $parts = \explode(' ', \trim($directive));
      if (\count($parts) <= 1) {
        continue;
      }
      $name = \array_shift($parts);
      $directives[$name] = $parts;
    }

    return $directives;
  }

  /**
   * Detects if the 'unsafe-inline' is prevented for a directive.
   */
  private function authorizesInline(array $directivesSet, string $type): bool {
    if (isset($directivesSet[$type])) {
      $directives = $directivesSet[$type];
    }
    elseif (NULL === $directives = $this->getDirectiveFallback($directivesSet, $type)) {
      return FALSE;
    }

    return \in_array('\'unsafe-inline\'', $directives, TRUE) && !$this->hasHashOrNonce($directives);
  }

  /**
   * Check if a directive set contains a hash or a nonce.
   */
  private function hasHashOrNonce(array $directives): bool {
    foreach ($directives as $directive) {
      if (!\str_ends_with($directive, '\'')) {
        continue;
      }
      if (\str_starts_with($directive, '\'nonce-')) {
        return TRUE;
      }
      if (\in_array(\substr($directive, 0, 8), [
        '\'sha256-',
        '\'sha384-',
        '\'sha512-',
      ], TRUE)) {
        return TRUE;
      }
    }

    return FALSE;
  }

  /**
   * Gets the fallback directive for a given directive set.
   */
  private function getDirectiveFallback(array $directiveSet, string $type): array|null {
    if (\in_array($type, [
      'script-src-elem',
      'style-src-elem',
    ], TRUE) || !isset($directiveSet['default-src'])) {
      // Let the browser fallback on it's own.
      return NULL;
    }

    return $directiveSet['default-src'];
  }

  /**
   * Retrieves the Content-Security-Policy headers.
   *
   * Either X-Content-Security-Policy or Content-Security-Policy) from a
   * response.
   */
  private function getCspHeaders(Response $response): array {
    $headers = [];

    if ($response->headers->has('Content-Security-Policy')) {
      $headers['Content-Security-Policy'] = $this->parseDirectives($response->headers->get('Content-Security-Policy'));
    }

    if ($response->headers->has('Content-Security-Policy-Report-Only')) {
      $headers['Content-Security-Policy-Report-Only'] = $this->parseDirectives($response->headers->get('Content-Security-Policy-Report-Only'));
    }

    if ($response->headers->has('X-Content-Security-Policy')) {
      $headers['X-Content-Security-Policy'] = $this->parseDirectives($response->headers->get('X-Content-Security-Policy'));
    }

    return $headers;
  }

}
