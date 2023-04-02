import { Link, Outlet, useLoaderData } from "react-router-dom";

export default function DrupalIndex() {
  const { projects } = useLoaderData();

  return (<div className="drupal">
    <ul className="drupal menu-items">
      {projects.length > 0 && projects.map(each => <li key={each.nid} className={`drupal menu-item menu-item--${each.machine_name}`}>
        <Link to={each.url_alias}>{each.title}</Link>
        <ul className={`drupal project--build-steps project--build-steps--${each.machine_name}`}>
          {each.build_steps.map(step => <li key={step.nid} className={`drupal project--build-step project--build-step--${each.machine_name}`}>
            <Link to={step.url_alias}>{step.title}</Link>
          </li>)}
        </ul>
      </li>
      )}
    </ul>
    <Outlet />
  </div>);
}

export async function loader() {
  const response = await fetch('https://hainguyenphc.com.ddev.site/jsonapi/projects-by-category/drupal', { mode: 'cors' });
  const json = await response.json();
  return { projects: json.data };
}
