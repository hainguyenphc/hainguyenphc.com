import { useState } from "react";
import Accordion from 'react-bootstrap/Accordion';
import { Link, Outlet, useLoaderData, useNavigate } from "react-router-dom";

export default function DrupalIndex() {
  const navigate = useNavigate();
  const [activeProjectID, setActiveProjectID] = useState(null);
  const { projects } = useLoaderData();
  return (<div className="drupal">
    <Accordion className="drupal menu-items" onSelect={(activeId) => {
      if (!activeId) {
        activeId = activeProjectID;
      } else {
        setActiveProjectID(activeId);
      }
      const match = projects.filter(each => Number.parseInt(each.nid) === Number.parseInt(activeId));
      navigate(`projects/${match[0].machine_name}`) 
    }}>
      {projects.length > 0 && projects.map(project => <Accordion.Item eventKey={project.nid} key={project.nid} className={`drupal menu-item menu-item--${project.machine_name}`}>
        <Accordion.Header>
          {project.title}
        </Accordion.Header>
        <Accordion.Body>
          <ul className={`drupal project--build-steps project--build-steps--${project.machine_name}`}>
            {project.build_steps.map(step => <li key={step.nid} className={`drupal project--build-step project--build-step--${project.machine_name}`}>
              <Link to={step.url_alias}>{step.title}</Link>
            </li>)}
          </ul>
        </Accordion.Body>
      </Accordion.Item>)}
    </Accordion>
    <Outlet />
  </div>);
}

export async function loader() {
  const response = await fetch('https://hainguyenphc.com.ddev.site/jsonapi/projects-by-category/drupal', { mode: 'cors' });
  const json = await response.json();
  return { projects: json.data };
}
