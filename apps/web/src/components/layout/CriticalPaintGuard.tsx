/** Blocking first-paint guard — hide UI until stylesheets + fonts are ready. */
const CRITICAL_CSS = `
html.sf-loading{background:#fdfcf9}
html.sf-loading body{opacity:0!important;pointer-events:none!important}
html.sf-nav-loading #sf-page{opacity:0;pointer-events:none}
html.sf-ready body,html.sf-ready #sf-page{opacity:1;transition:opacity .2s ease}
button,input,select,textarea{font:inherit;color:inherit}
button{border:none;background:transparent;padding:0;cursor:pointer}
.sf-site-header__icon-btn{display:inline-flex;align-items:center;justify-content:center;width:2.75rem;height:2.75rem;border-radius:999px;border:1px solid #e2e8f0;background:#fff;color:#374151}
#mobileMenuBtn{display:inline-flex;align-items:center;justify-content:center;width:3rem;height:3rem;border:none;border-radius:999px;background:transparent;color:#374151}
.lx-catnav__list{display:flex;align-items:center;flex-wrap:nowrap;overflow:visible;width:100%;max-width:100%;min-width:0;list-style:none;margin:0;padding:.35rem 1.5rem;border:1.5px solid rgba(194,160,90,.42);border-radius:999px;background:#fff;box-sizing:border-box}
@media (max-width:1023.98px){.lx-catnav__list{overflow-x:auto;overflow-y:hidden}.lx-catnav__mega{display:none!important}}
.lx-catnav__item{flex:0 0 auto;list-style:none;position:static}
.lx-catnav__link{display:inline-flex;align-items:center;text-decoration:none;white-space:nowrap}
.hp-fnp-icons__scroll{display:flex;gap:.65rem;overflow-x:auto;padding:.25rem .75rem}
.hp-fnp-icons__item{display:flex;flex-direction:column;align-items:center;gap:.45rem;flex:0 0 auto;min-width:4.75rem;text-decoration:none;color:#1e293b}
.homepage-premium .hp-fnp-icons__img{border-radius:50%;border:1.5px solid rgba(194,160,90,.55);padding:.22rem;background:#fff}
.homepage-premium .hp-fnp-icons__img--icon{display:inline-flex;align-items:center;justify-content:center;width:4.15rem;height:4.15rem;aspect-ratio:1}
.hp-fnp-icons__label{font-size:.6875rem;font-weight:600;text-align:center;line-height:1.25}
.sf-offer__badge,.sf-banner__badge{display:inline-flex;align-items:center;border:none;border-radius:999px;background:#1f5138;color:#fff;font-size:.65rem;font-weight:800;text-transform:uppercase}
.sf-icon{display:inline-block;flex-shrink:0;vertical-align:middle}
html.sf-loading .fas,html.sf-loading .fab,html.sf-loading .far,html.sf-loading .material-icons-outlined{visibility:hidden}
`.replace(/\s+/g, ' ');

/**
 * Reveal when CSSOM + fonts are ready — do NOT wait for full window.load
 * (images), which either delays forever or times out into FOUC.
 */
const BOOT_SCRIPT = `(function(){var h=document.documentElement;if(h.classList.contains('sf-ready'))return;h.classList.add('sf-loading');var done=false;function reveal(){if(done)return;done=true;requestAnimationFrame(function(){requestAnimationFrame(function(){h.classList.remove('sf-loading');h.classList.add('sf-ready','fonts-ready')})})}function waitLink(l){try{if(l.sheet)return Promise.resolve()}catch(e){}return new Promise(function(res){l.addEventListener('load',res,{once:true});l.addEventListener('error',res,{once:true})})}function waitCss(){var links=[].slice.call(document.querySelectorAll('link[rel="stylesheet"]'));return Promise.all(links.map(waitLink))}var fonts=(document.fonts&&document.fonts.ready)?document.fonts.ready:Promise.resolve();Promise.all([waitCss(),fonts]).then(reveal).catch(reveal);setTimeout(reveal,2200)})();`;

export function CriticalPaintGuard() {
  return (
    <>
      <style dangerouslySetInnerHTML={{ __html: CRITICAL_CSS }} />
      <script dangerouslySetInnerHTML={{ __html: BOOT_SCRIPT }} />
    </>
  );
}
