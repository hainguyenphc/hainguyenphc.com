/**
 * @file themes/custom/reactportfolio/frontend/src/routes/root.jsx
 */

import Prism from "prismjs";
import { useEffect } from "react";
import Nav from 'react-bootstrap/Nav';
import { LinkContainer } from 'react-router-bootstrap';
import { Outlet, useLocation } from "react-router-dom";

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
  // {
  //   id: 'ios',
  //   slug: 'ios',
  //   title: 'iOS/MacOS',
  // },
];

export default function Root() {
  const HOVER_UNDERLINE_ANIMATION = "hover-underline-animation";
  const location = useLocation();
  useEffect(() => {
    Prism.highlightAll();
    document.title = 'Hai Nguyen Portfolio';
  }, [location.pathname]); // Whenever the location changes, we re-trigger the highlighting.
  return (<>
    <Nav className="mb-4" defaultActiveKey={''}>
      {TOPICS.map(each => <Nav.Item key={`${each.slug}`} >
        <LinkContainer to={`${each.slug}`} id={each.slug === '' ? 'nav-link--home' : null}>
          <Nav.Link className={`${HOVER_UNDERLINE_ANIMATION}` + (location.pathname.includes(each.slug) && each.slug !== '' ? ' active' : '')}>
            {each.title} {each.slug === '' ? <i className="fa fa-house"></i> : ''}
          </Nav.Link>
        </LinkContainer>
      </Nav.Item>)}
      {/* Resume */}
      <Nav.Item>
        <a href="/Hai_Nguyen_resume.pdf" className={`${HOVER_UNDERLINE_ANIMATION} nav-link`} target="_blank" rel="noreferrer">My Resume</a>
      </Nav.Item>
    </Nav>
    <Outlet />
  </>);
}
