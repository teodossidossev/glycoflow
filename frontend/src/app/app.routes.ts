import { Routes } from '@angular/router';

export const routes: Routes = [
  {
    path: '',
    redirectTo: 'home',
    pathMatch: 'full',
  },
  {
    path: 'home',
    loadComponent: () => import('./features/home/home').then((m) => m.Home),
  },
  {
    path: 'history',
    loadComponent: () => import('./features/history/history').then((m) => m.History),
  },
  {
    path: 'analysis',
    loadComponent: () => import('./features/analysis/analysis').then((m) => m.Analysis),
  },
  {
    path: 'settings',
    loadComponent: () => import('./features/settings/settings').then((m) => m.Settings),
  },
  {
    path: '**',
    redirectTo: 'home',
  },
];
