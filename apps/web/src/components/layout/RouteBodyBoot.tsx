import { routeBodyBootScript } from '@/lib/route-css';

/** Sync body class/background on first paint without server headers(). */
export function RouteBodyBoot() {
  return <script dangerouslySetInnerHTML={{ __html: routeBodyBootScript() }} />;
}
