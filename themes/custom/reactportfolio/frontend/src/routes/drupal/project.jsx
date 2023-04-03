import { useEffect } from "react";
import { useLoaderData, useOutletContext } from "react-router-dom";

export default function DrupalProject() {
  const { project } = useLoaderData();
  const { title, body } = project;
  return (<><h1 dangerouslySetInnerHTML={{ __html: title }}></h1><div className={`drupal project-index project--${project.machine_name}`} dangerouslySetInnerHTML={{ __html: body }}></div></>);
}

export async function loader({ params }) {
  const { project } = params;
  const response = await fetch(`https://hainguyenphc.com.ddev.site/jsonapi/projects/${project}`, { mode: 'cors' });
  const json = await response.json();
  return { project: json.data };
}
