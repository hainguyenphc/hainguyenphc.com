/**
 * @file themes/custom/reactportfolio/frontend/src/routes/drupal/project-build-step.jsx
 */

import { useLoaderData } from "react-router-dom";
import getHost from "../../utils/host";
import { useEffect } from "react";

export default function DrupalProjectBuildStep() {
  const { buildStep } = useLoaderData();
  const { title, body } = buildStep;
  useEffect(() => {
    document.title = `Drupal: ${title}`;
  });
  return (<><h1 dangerouslySetInnerHTML={{ __html: title }}></h1><div dangerouslySetInnerHTML={{ __html: body }}></div></>);
}

export async function loader({ params }) {
  const host = getHost();
  const { project, buildStep } = params;
  const response = await fetch(`${host}/jsonapi/projects/${project}/build-steps/${buildStep}`, { mode: 'cors' });
  const json = await response.json();
  return { buildStep: json.data };
}
