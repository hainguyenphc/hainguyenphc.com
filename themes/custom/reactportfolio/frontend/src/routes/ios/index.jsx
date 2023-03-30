import { Link, Outlet } from "react-router-dom";

const PERSONAL_PROJECTS = [
  {
    id: '101',
    slug: 'cce',
    title: 'Canadian Citizenship Exam',
  }
];

export default function IOSIndex() {
  return (<div>
    iOS Index
    <ul>
      {PERSONAL_PROJECTS.map(each => <li key={each.id}>
          {/* Do not leave any preceding forward slash. */}
          <Link to={`personal-projects/${each.slug}`}>{each.title}</Link>
        </li> 
      )}
    </ul>
    <Outlet />
  </div>);
}
