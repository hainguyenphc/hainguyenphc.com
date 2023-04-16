/**
 * @file themes/custom/reactportfolio/frontend/src/routes/drupal/project-build-step.jsx
 * @see modules/custom/hnp_project/hnp_project.routing.yml
 * @route hnp_project.get_build_step 
 * 
 * Renders a Project Build Step of a Drupal Project.
 */

import { useLoaderData } from "react-router-dom";
import getHost from "../../utils/host";
import { useEffect } from "react";
import RelatedArticles from "../../components/RelatedArticles";

export default function DrupalProjectBuildStep() {
  const { buildStep } = useLoaderData();
  const { title, body } = buildStep;
  const related = Object.values(buildStep.related || {});
  useEffect(() => {
    document.title = `Drupal: ${title}`;
  });
  return (<>
    <h1 dangerouslySetInnerHTML={{ __html: title }}></h1>
    <div dangerouslySetInnerHTML={{ __html: body }}></div>
    <RelatedArticles related={related} />
  </>);
}

export async function loader({ params }) {
  const host = getHost();
  const { project, buildStep } = params;
  const response = await fetch(`${host}/jsonapi/projects/${project}/build-steps/${buildStep}`, { mode: 'cors' });
  const json = await response.json();
  return { buildStep: json.data };
}
