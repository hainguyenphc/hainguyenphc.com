import { Link } from "react-router-dom";

const CLIENT_PROJECTS = [
  {
    id: '1',
    title: 'KHSC',
  },
  {
    id: '2',
    title: 'Kaufman Hall',
  },
];

export default function Root() {
  return (<div>
    <ul>
      {CLIENT_PROJECTS.map(each => <li>
          <Link to={`client-projects/${each.id}`}>{each.title}</Link>
        </li> 
      )}
    </ul>
  </div>);
}
