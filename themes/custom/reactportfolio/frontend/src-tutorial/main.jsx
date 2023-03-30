import React from 'react'
import ReactDOM from 'react-dom/client'
import { createBrowserRouter, RouterProvider } from 'react-router-dom'
import ErrorPage from './error-page'
import './index.css'
import Contact, { loader as ContactLoader, action as ContactAction } from './routes/contact'
import EditContact, { action as EditAction } from './routes/edit'
import { action as DestroyAction } from './routes/destroy';
import Root, { loader as RootLoader, action as RootAction } from './routes/root'
import Index from './routes'

const router = createBrowserRouter([
  {
    path: '/',
    element: <Root />,
    errorElement: <ErrorPage />,
    loader: RootLoader,
    // <Form> component prevents the browser from sending a request to server,
    // and sends it to the route action instead.
    action: RootAction,
    children: [
      {
        errorElement: <ErrorPage />,
        children: [
          // the router to match and render this route when the user is at the parent route's exact path, so there are no other child routes to render in the <Outlet>.
          {
            index: true,
            element: <Index />
          },
          {
            path: '/contacts/:contactId',
            element: <Contact />,
            loader: ContactLoader,
            action: ContactAction,
          },
          {
            path: '/contacts/:contactId/edit',
            element: <EditContact />,
            // You might note we reused the contactLoader for this route. 
            // This is only because we're being lazy in the tutorial.
            // There is no reason to attempt to share loaders among routes, they usually have their own.
            // from './routes/contact'
            loader: ContactLoader,
            action: EditAction,
          },
          {
            path: '/contacts/:contactId/destroy',
            action: DestroyAction,
          }
        ],
      },
    ],
  },
  // {
  //   path: '/contacts/:contactId',
  //   element: <Contact />
  // }
]);

ReactDOM.createRoot(document.getElementById('root')).render(
  <React.StrictMode>
    {/* <App /> */}
    <RouterProvider router={router} />
  </React.StrictMode>,
)
