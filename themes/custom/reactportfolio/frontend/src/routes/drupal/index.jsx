/**
 * @file themes/custom/reactportfolio/frontend/src/routes/drupal/index.jsx
 */

import { useState } from "react";
import Accordion from 'react-bootstrap/Accordion';
import { Link, Outlet, useLoaderData, useLocation, useNavigate } from "react-router-dom";
import getHost from "../../utils/host";
// import Code from "../../components/Code";

// const JSCode = `const App = props => {
//   return (
//     <div>
//       <h1> Prism JS </h1>
//       <div>Awesome Syntax Highlighter</div>
//     </div>
//   );
// };
// `;

export default function DrupalIndex() {
  const navigate = useNavigate();
  const location = useLocation();
  const [activeProjectID, setActiveProjectID] = useState(null);
  const [show, setShow] = useState(true);
  const { projects } = useLoaderData();
  return (<div className="drupal drupal-index">
    <div id="sidebar">
      <Accordion id="accordion" className="drupal menu-items" onSelect={(activeId) => {
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
              {project.build_steps.map(step => <li key={step.nid} className={`drupal project--build-step project--build-step--${project.machine_name}` + (location.pathname === step.url_alias ? " chosen" : null)}>
                <Link to={step.url_alias}>{step.title}</Link>
              </li>)}
            </ul>
          </Accordion.Body>
        </Accordion.Item>)}
      </Accordion>
    </div>
    
    <div id="main">
      <Outlet context={[show, setShow]} />
      {/* <Code code={JSCode} language="javascript" /> */}
    </div>
    
  </div>);
}

export async function loader() {
  const host = getHost();
  const response = await fetch(`${host}/jsonapi/projects-by-category/drupal`, { mode: 'cors' });
  const json = await response.json();
  return { projects: json.data };
}
