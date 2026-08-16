/** Blocking first-paint guard — must run before any external CSS/fonts. */
const CRITICAL_CSS = `
html:not(.sf-ready) .sf-site-header,
html:not(.sf-ready) .lx-catnav,
html:not(.sf-ready) .hp-fnp-firstview,
html:not(.sf-ready) .sf-bottom-nav {
  visibility: hidden;
}
html.sf-ready .sf-site-header,
html.sf-ready .lx-catnav,
html.sf-ready .hp-fnp-firstview,
html.sf-ready .sf-bottom-nav {
  visibility: visible;
}
button,input,select,textarea{font:inherit;color:inherit}
button{border:none;background:transparent;padding:0;cursor:pointer}
.sf-site-header__icon-btn{display:inline-flex;align-items:center;justify-content:center;width:2.75rem;height:2.75rem;border-radius:999px;border:1px solid #e2e8f0;background:#fff;color:#374151}
#mobileMenuBtn{display:inline-flex;align-items:center;justify-content:center;width:3rem;height:3rem;border:none;border-radius:999px;background:transparent;color:#374151}
.lx-catnav__list{list-style:none;margin:0;padding:.35rem 1.5rem;border:1.5px solid rgba(194,160,90,.42);border-radius:999px;background:#fff}
.lx-catnav__item{list-style:none}
.homepage-premium .hp-fnp-icons__img{border-radius:50%;border:1.5px solid rgba(194,160,90,.55);padding:.22rem;background:#fff}
.sf-offer__badge,.sf-banner__badge{display:inline-flex;align-items:center;border:none;border-radius:999px;background:#1f5138;color:#fff;font-size:.65rem;font-weight:800;text-transform:uppercase}
.sf-icon{display:inline-block;flex-shrink:0;vertical-align:middle}
.material-icons-outlined,.fas,.fab,.far{font-size:0!important;line-height:0!important;width:1em;height:1em;overflow:hidden}
html.fonts-ready .material-icons-outlined{font-size:24px!important;line-height:1!important}
html.fonts-ready .sf-bottom-nav .material-icons-outlined{font-size:1.35rem!important}
`.replace(/\s+/g, ' ');

const BOOT_SCRIPT = `(function(){function r(){document.documentElement.classList.add('sf-ready')}if(document.readyState==='loading'){document.addEventListener('DOMContentLoaded',r,{once:true})}else{r()}function f(){document.documentElement.classList.add('fonts-ready')}if(document.fonts&&document.fonts.ready){document.fonts.ready.then(f).catch(f)}else{f()}})();`;

export function CriticalPaintGuard() {
  return (
    <>
      <style dangerouslySetInnerHTML={{ __html: CRITICAL_CSS }} />
      <script dangerouslySetInnerHTML={{ __html: BOOT_SCRIPT }} />
    </>
  );
}
