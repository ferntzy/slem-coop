import { createBrowserRouter } from 'react-router';
import { Layout } from './components/Layout';
import { Home } from './pages/Home';
import { Loans } from './pages/Loans';
import { Deposits } from './pages/Deposits';
import { MembershipInfo } from './pages/MembershipInfo';
import { MembershipSteps } from './pages/MembershipSteps';
import { MembershipApply } from './pages/MembershipApply';
import { NewsEvents } from './pages/NewsEvents';
import { About } from './pages/About';
import { Contact } from './pages/Contact';
import { LoanCalculator } from './pages/LoanCalculator';
import { Login } from './pages/Login';
import { NotFound } from './pages/NotFound';
import { ServerError } from './pages/ServerError';

export const router = createBrowserRouter([
  {
    path: '/',
    Component: Layout,
    errorElement: <NotFound />,
    children: [
      { index: true, Component: Home },

      // Products & Services
      { path: 'products', Component: Loans },
      { path: 'products/loans', Component: Loans },
      { path: 'products/deposits', Component: Deposits },

      // Membership
      { path: 'membership', Component: MembershipInfo },
      { path: 'membership/info', Component: MembershipInfo },
      { path: 'membership/steps', Component: MembershipSteps },
      { path: 'membership/apply', Component: MembershipApply },

      // News & Updates
      { path: 'news', Component: NewsEvents },
      { path: 'news/events', Component: NewsEvents },

      // Other Pages
      { path: 'about', Component: About },
      { path: 'contact', Component: Contact },
      { path: 'calculator', Component: LoanCalculator },
      { path: 'login', Component: Login },
    ],
  },
  // Catch-all route outside of Layout for 404 pages without header/footer
  {
    path: '*',
    Component: NotFound,
  },
  // Server error route outside of Layout
  {
    path: '/500',
    Component: ServerError,
  },
]);
