/**
 * @file themes/custom/reactportfolio/frontend/src/main.jsx
 */

import 'bootstrap/dist/css/bootstrap.min.css';
import React from 'react';
import ReactDOM from 'react-dom/client';
import { createBrowserRouter, RouterProvider } from 'react-router-dom';
import "./index.css";
import Index from './routes';
import DrupalIndex, { loader as DrupalIndexLoader } from './routes/drupal';
import DrupalForeword from './routes/drupal/fore-word';
import DrupalProject, { loader as DrupalProjectLoader } from './routes/drupal/project';
import DrupalProjectBuildStep, { loader as DrupalProjectBuildStepLoader } from './routes/drupal/project-build-step';
import DrupalTutorial from './routes/drupal/tutorial';
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
            loader: DrupalIndexLoader,
            children: [
              {
                path: '',
                element: <DrupalForeword />,
              },
              {
                path: 'projects/:project',
                element: <DrupalProject />,
                loader: DrupalProjectLoader,
              },
              {
                path: 'projects/:project/:buildStep',
                element: <DrupalProjectBuildStep />,
                loader: DrupalProjectBuildStepLoader,
              },
              {
                path: 'tutorials/:tutorial',
                element: <DrupalTutorial />,
                loader: null,
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