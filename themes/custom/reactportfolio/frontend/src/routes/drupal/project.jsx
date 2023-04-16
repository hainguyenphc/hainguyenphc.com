/**
 * @file themes/custom/reactportfolio/frontend/src/routes/drupal/project.jsx
 * @see modules/custom/hnp_project/hnp_project.routing.yml
 * @route hnp_project.get_project
 * 
 * Renders a Drupal Project.
 */

import { useLoaderData } from "react-router-dom";
import getHost from "../../utils/host";
import { useEffect } from "react";
import RelatedArticles from "../../components/RelatedArticles";

export default function DrupalProject() {
  const { project } = useLoaderData();
  const { title, body } = project;
  const related = Object.values(project.related || {});
  useEffect(() => {
    document.title = `Drupal: ${title}`;
  });
  return (<>
    <h1 dangerouslySetInnerHTML={{ __html: title }}></h1>
    <div className={`drupal project-index project--${project.machine_name}`} dangerouslySetInnerHTML={{ __html: body }}></div>
    <RelatedArticles related={related} />
  </>);
}

export async function loader({ params }) {
  const host = getHost();
  const { project } = params;
  const response = await fetch(`${host}/jsonapi/projects/${project}`, { mode: 'cors' });
  const json = await response.json();
  return { project: json.data };
}
