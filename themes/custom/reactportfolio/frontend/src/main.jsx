import React from 'react';
import ReactDOM from 'react-dom/client';
import { createBrowserRouter, RouterProvider } from 'react-router-dom';
import Index from './routes';
import DrupalIndex from './routes/drupal';
import DrupalClientProject, { loader as DrupalClientProjectLoader } from './routes/drupal/client-project';
import DrupalProjectBuildStep, { loader as DrupalProjectBuildStepLoader } from './routes/drupal/project-build-step';
import IOSIndex from './routes/ios';
import IOSTutorial from './routes/ios/tutorial';
import PersonalProject from './routes/personal-project';
import Root from './routes/root';

// ==============================================

const router = createBrowserRouter([
  {
    path: '/',
    element: <Root />,
    children: [
      {
        errorElement: null,
        children: [
          {
            index: true,
            element: <Index />,
          },
          // Drupal "subsite"
          {
            path: '/drupal',
            element: <DrupalIndex />,
            children: [
              {
                path: 'projects/:project',
                element: <DrupalClientProject />,
                loader: DrupalClientProjectLoader,
                action: null,
                children: [],
              },
              {
                path: 'projects/:project/:buildStep',
                element: <DrupalProjectBuildStep />,
                loader: DrupalProjectBuildStepLoader,
              }
            ],
          },
          // // iOS "subsite"
          // {
          //   path: '/ios',
          //   element: <IOSIndex />,
          //   children: [
          //     {
          //       path: 'personal-projects/:projectId',
          //       element: <PersonalProject />,
          //     },
          //     {
          //       path: 'tutorials/:toturialId',
          //       element: <IOSTutorial />,
          //     },
          //   ],
          // }
        ],
      },
    ],
  }
]);

// ==============================================

ReactDOM.createRoot(document.getElementById('root')).render(
  <React.StrictMode>
    <RouterProvider router={router} />
  </React.StrictMode>
);
