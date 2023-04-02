import { useLoaderData } from "react-router-dom";

export default function DrupalProjectBuildStep() {
  const { buildStep } = useLoaderData();
  return (<div>{buildStep.title}</div>);
}

export async function loader({ params }) {
  const { project, buildStep } = params;
  const response = await fetch(`https://hainguyenphc.com.ddev.site/jsonapi/projects/${project}/build-steps/${buildStep}`, { mode: 'cors' });
  const json = await response.json();
  return { buildStep: json.data };
}
