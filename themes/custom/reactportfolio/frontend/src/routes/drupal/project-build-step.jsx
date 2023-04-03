import { useLoaderData } from "react-router-dom";

export default function DrupalProjectBuildStep() {
  const { buildStep } = useLoaderData();
  const { title, body } = buildStep;
  return (<><h1 dangerouslySetInnerHTML={{ __html: title }}></h1><div dangerouslySetInnerHTML={{ __html: body }}></div></>);
}

export async function loader({ params }) {
  const { project, buildStep } = params;
  const response = await fetch(`https://hainguyenphc.com.ddev.site/jsonapi/projects/${project}/build-steps/${buildStep}`, { mode: 'cors' });
  const json = await response.json();
  return { buildStep: json.data };
}
