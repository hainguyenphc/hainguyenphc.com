import { useEffect, useState } from "react";
import { Link, useLoaderData } from "react-router-dom";

export default function DrupalClientProject() {
  const { project } = useLoaderData();
  const [buildSteps, setBuildSteps] = useState([]);

  useEffect(() => {
    let ignore = false;
    async function getBuildSteps(project) {
      await fetch(`https://hainguyenphc.com.ddev.site/jsonapi/projects/${project}/build-steps`, { mode: 'cors' })
        .then(async (response) => {
          return response.json();
        })
        .then((json) => {
          if (!ignore) {
            setBuildSteps(json.data);
          }
        });
    }
    getBuildSteps(project.machine_name);
    return () => { ignore = true; };
  }, []);

  return (<div>
    {project.title}
    {project.body}
    {/* Build steps */}
    <div>
      <ul>
        {buildSteps.map(each => <li key={each.nid}>
          <Link to={each.url_alias}>{each.title}</Link>
        </li>)}
      </ul>
    </div>
  </div>);
}

export async function loader({ params }) {
  const { project } = params;
  const response = await fetch(`https://hainguyenphc.com.ddev.site/jsonapi/projects/${project}`, { mode: 'cors' });
  const json = await response.json();
  return { project: json.data };
}
