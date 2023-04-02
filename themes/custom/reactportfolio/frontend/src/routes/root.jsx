import { Link, Outlet } from "react-router-dom";

const TOPICS = [
  // {
  //   id: 'ios',
  //   slug: 'ios',
  //   title: 'iOS Developement',
  // },
  {
    id: 'drupal',
    slug: 'drupal',
    title: 'Drupal Development',
  }
];

export default function Root() {
  return (<>
    <ul>
      {TOPICS.map(each => <li key={each.id}>
        <Link to={`${each.slug}`}>{each.title}</Link>
      </li>
      )}
    </ul>
    <Outlet />
  </>);
}
