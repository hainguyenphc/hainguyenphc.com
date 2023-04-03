import { Link, Outlet } from "react-router-dom";
import { LinkContainer } from 'react-router-bootstrap';
import Nav from 'react-bootstrap/Nav';

const TOPICS = [
  {
    id: 'ios',
    slug: 'ios',
    title: 'iOS Developement',
  },
  {
    id: 'drupal',
    slug: 'drupal',
    title: 'Drupal Development',
  }
];

export default function Root() {
  return (<>
    <Nav className="mb-4">
      {TOPICS.map(each => <Nav.Item key={`${each.slug}`}>
        <LinkContainer to={`${each.slug}`}>
          <Nav.Link>{each.title}</Nav.Link>
        </LinkContainer>
      </Nav.Item>)}
    </Nav>
    <Outlet />
  </>);
}
