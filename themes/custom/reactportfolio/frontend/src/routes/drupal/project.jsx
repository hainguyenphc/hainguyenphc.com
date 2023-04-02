import { useLoaderData } from "react-router-dom";

export default function DrupalProject() {
  const { project } = useLoaderData();

  return (<div className={`drupal project-index project--${project.machine_name}`}>
    {/* Title */}
    <div className={`drupal project--title project--title--${project.machine_name}`} dangerouslySetInnerHTML={{ __html: project.title }}></div>
    {/* Body */}
    <div className={`drupal project--body project--body--${project.machine_name}`} dangerouslySetInnerHTML={{ __html: project.body }}></div>
  </div>);
}

export async function loader({ params }) {
  const { project } = params;
  const response = await fetch(`https://hainguyenphc.com.ddev.site/jsonapi/projects/${project}`, { mode: 'cors' });
  const json = await response.json();
  return { project: json.data };
}
