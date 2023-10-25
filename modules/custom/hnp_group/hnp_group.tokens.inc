<?php

use Drupal\Core\Render\BubbleableMetadata;
use Drupal\Core\Url;
use Drupal\group\Entity\Group;
use Drupal\group\Entity\GroupMembership;
use Drupal\node\Entity\Node;

/**
 * @file modules/custom/hnp_group/hnp_group.tokens.inc
 */

function hnp_group_token_info() {
  return [
    'types' => [
      'node' => [
        'name' => t('Parent Node'),
        'description' => t('The parent node'),
      ],
    ],
    'tokens' => [
      'node' => [
        'parent_node' => [
          'name' => t('Parent node in group context'),
          'description' => t('The parent node (if any). See hnp_group module.'),
        ],
      ],
    ],
  ];
}

function hnp_group_tokens($type, $tokens, array $data, array $options, BubbleableMetadata $bubbleable_metadata) {
  $replacements = [];

  if ($type == 'node' && !empty($data['node'])) {
		foreach ($tokens as $name => $original) {
      $node = $data['node'];
			switch ($name) {
        case 'entitygroupfield':
          if ($node instanceof Node && $node->bundle() === 'article') {
            if (!$node->field_parent->isEmpty()) {
              $replacements[$original] = '';
            }
          }
          break;
				case 'parent_node':
          if ($node instanceof Node && $node->bundle() === 'article') {
            if (!$node->field_parent->isEmpty()) {
              $parent_node = $node->field_parent->first()->entity;
              $url = $parent_node->toUrl()->toString();
              if ($parent_node->hasField('entitygroupfield') && $parent_node->entitygroupfield->count() > 0) {
                $membership_id = $parent_node->entitygroupfield->target_id;
                $membership = GroupMembership::load($membership_id);
                $group = Group::load($membership->gid->target_id);
                $group_url = $group->toUrl()->toString();
                $url = str_replace($group_url, '', $url);
                $replacements[$original] = $url;
              }
            }
          }
					break;
			}
		}
	}

  return $replacements;
}

//[node:entitygroupfield]/[node:field_parent:entity:title]/[node:title] #}
