import { useRouteError } from "react-router-dom";

export default function ComingSoonPage() {
  const error = useRouteError();
  console.log(error);
  return (<div id="error-page">
    <h1>This is coming soon.</h1>
    <p>Sorry for this inconvience. We are working on it. Please check back later.</p>
  </div>);
}
