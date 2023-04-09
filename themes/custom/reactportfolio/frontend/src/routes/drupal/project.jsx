/**
 * @file themes/custom/reactportfolio/frontend/src/routes/drupal/project.jsx
 */

import { useLoaderData } from "react-router-dom";
import getHost from "../../utils/host";
import { useEffect } from "react";

export default function DrupalProject() {
  const { project } = useLoaderData();
  const { title, body } = project;
  useEffect(() => {
    document.title = `Drupal: ${title}`;
  });
  return (<><h1 dangerouslySetInnerHTML={{ __html: title }}></h1><div className={`drupal project-index project--${project.machine_name}`} dangerouslySetInnerHTML={{ __html: body }}></div></>);
}

export async function loader({ params }) {
  const host = getHost();
  const { project } = params;
  const response = await fetch(`${host}/jsonapi/projects/${project}`, { mode: 'cors' });
  const json = await response.json();
  return { project: json.data };
}
