import { Link, Outlet } from "react-router-dom";


const CLIENT_PROJECTS = [
  {
    id: '1',
    slug: 'khsc',
    title: 'KHSC',
  },
  {
    id: '2',
    slug: 'kaufman',
    title: 'Kaufman Hall',
  },
];

const PERSONAL_PROJECTS = [
  {
    id: '101',
    slug: 'portfolio',
    title: 'Decoupled Drupal Portfolio Site',
  }
];

const DRUPAL_TOTURIALS = [
  {
    id: '1001',
    slug: 'hook-form-alter',
    title: 'Introduction to hook_form_alter hook',
  }
];

export default function DrupalIndex() {
  return (<div>
    Drupal Index
    <ul>
      {CLIENT_PROJECTS.map(each => <li key={each.id}>
          {/* Do not leave any preceding forward slash. */}
          <Link to={`client-projects/${each.slug}`}>{each.title}</Link>
        </li> 
      )}
    </ul>
    <ul>
      {PERSONAL_PROJECTS.map(each => <li key={each.id}>
          {/* Do not leave any preceding forward slash. */}
          <Link to={`personal-projects/${each.slug}`}>{each.title}</Link>
        </li> 
      )}
    </ul>
    <ul>
      {DRUPAL_TOTURIALS.map(each => <li key={each.id}>
          {/* Do not leave any preceding forward slash. */}
          <Link to={`tutorials/${each.slug}`}>{each.title}</Link>
        </li> 
      )}
    </ul>
    <Outlet />
  </div>);
}
