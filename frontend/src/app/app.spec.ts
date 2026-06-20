import { TestBed } from '@angular/core/testing';
import { provideRouter } from '@angular/router';
import { RouterTestingHarness } from '@angular/router/testing';
import { Location } from '@angular/common';

import { App } from './app';
import { routes } from './app.routes';

describe('App', () => {
  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [App],
      providers: [provideRouter(routes)],
    }).compileComponents();
  });

  it('should create the app', () => {
    const fixture = TestBed.createComponent(App);
    const app = fixture.componentInstance;
    expect(app).toBeTruthy();
  });

  it('should render the application title', async () => {
    const fixture = TestBed.createComponent(App);
    await fixture.whenStable();
    const compiled = fixture.nativeElement as HTMLElement;
    expect(compiled.querySelector('.app-title')?.textContent).toContain('GlycoFlow');
  });

  it('should provide a router outlet', () => {
    const fixture = TestBed.createComponent(App);
    fixture.detectChanges();
    const compiled = fixture.nativeElement as HTMLElement;
    expect(compiled.querySelector('router-outlet')).not.toBeNull();
  });

  it('should render the four navigation links', () => {
    const fixture = TestBed.createComponent(App);
    fixture.detectChanges();
    const compiled = fixture.nativeElement as HTMLElement;
    const links = compiled.querySelectorAll('.app-nav__link');
    expect(links.length).toBe(4);
    const labels = Array.from(links).map((link) => link.textContent?.trim());
    expect(labels).toEqual(['Home', 'History', 'Analysis', 'Settings']);
  });

  it('should redirect the default route to /home', async () => {
    await RouterTestingHarness.create('');
    const location = TestBed.inject(Location);
    expect(location.path()).toBe('/home');
  });

  it('should redirect an unknown route to /home', async () => {
    await RouterTestingHarness.create('/does-not-exist');
    const location = TestBed.inject(Location);
    expect(location.path()).toBe('/home');
  });
});
