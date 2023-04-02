import { useEffect, useState } from "react";
import { Link, Outlet } from "react-router-dom";
import IS_DEBUGGING from "../../utils/debugging";

export default function DrupalIndex() {
  const [projects, setProjects] = useState([]);

  useEffect(() => {
    let ignore = false;
    async function getProjectsByCategory(category) {
      await fetch(`https://hainguyenphc.com.ddev.site/jsonapi/projects-by-category/${category}`, { mode: 'cors' })
        .then(async (response) => {
          return response.json();
        })
        .then((json) => {
          if (!ignore) {
            setProjects(json.data);
          }
        });
    }
    getProjectsByCategory('drupal');
    return () => { ignore = true; };
  }, []);
  
  return (<div>
    { IS_DEBUGGING ? "Drupal Index" : "" }
    <ul>
      {projects.length > 0 && projects.map(each => <li key={each.nid}>
        <Link to={each.url_alias}>{each.title}</Link>
      </li>
      )}
    </ul>
    <Outlet />
  </div>);
}
