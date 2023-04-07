/**
 * @file themes/custom/reactportfolio/frontend/src/routes/root.jsx
 */

import { Outlet, useLocation } from "react-router-dom";
import { LinkContainer } from 'react-router-bootstrap';
import Nav from 'react-bootstrap/Nav';

const TOPICS = [
  {
    id: 'home',
    slug: '',
    title: 'Home'
  },
  {
    id: 'drupal',
    slug: 'drupal',
    title: 'Drupal',
  },
  {
    id: 'ios',
    slug: 'ios',
    title: 'iOS/MacOS',
  },
];

export default function Root() {
  const HOVER_UNDERLINE_ANIMATION = "hover-underline-animation";
  const location = useLocation();
  return (<>
    <Nav className="mb-4" defaultActiveKey={''}>
      {TOPICS.map(each => <Nav.Item key={`${each.slug}`} >
        <LinkContainer to={`${each.slug}`} id={each.slug === '' ? 'nav-link--home' : null}>
          <Nav.Link className={`${HOVER_UNDERLINE_ANIMATION}` + (location.pathname.includes(each.slug) && each.slug !== '' ? ' active' : '')}>{each.title}</Nav.Link>
        </LinkContainer>
      </Nav.Item>)}
    </Nav>
    <Outlet />
  </>);
}
