/** Blocking first-paint guard — hide page until CSS + fonts are ready (prevents reload flash). */
const CRITICAL_CSS = `
html.sf-loading{background:#fdfcf9}
html.sf-loading body{visibility:hidden}
html.sf-loading .sf-site-header,
html.sf-loading .lx-catnav,
html.sf-loading .hp-fnp-firstview,
html.sf-loading .sf-bottom-nav{visibility:hidden}
button,input,select,textarea{font:inherit;color:inherit}
button{border:none;background:transparent;padding:0;cursor:pointer}
.sf-site-header__icon-btn{display:inline-flex;align-items:center;justify-content:center;width:2.75rem;height:2.75rem;border-radius:999px;border:1px solid #e2e8f0;background:#fff;color:#374151}
#mobileMenuBtn{display:inline-flex;align-items:center;justify-content:center;width:3rem;height:3rem;border:none;border-radius:999px;background:transparent;color:#374151}
.lx-catnav__list{display:flex;align-items:center;flex-wrap:nowrap;overflow-x:auto;list-style:none;margin:0;padding:.35rem 1.5rem;border:1.5px solid rgba(194,160,90,.42);border-radius:999px;background:#fff}
.lx-catnav__item{flex:0 0 auto;list-style:none}
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

const BOOT_SCRIPT = `(function(){var h=document.documentElement;h.classList.add('sf-loading');var loadDone=false,fontsDone=false,done=false;function reveal(){if(done)return;done=true;h.classList.remove('sf-loading');h.classList.add('sf-ready','fonts-ready')}function maybe(){if(!loadDone||!fontsDone)return;requestAnimationFrame(function(){requestAnimationFrame(reveal)})}function onLoad(){loadDone=true;maybe()}if(document.readyState==='complete'){onLoad()}else{window.addEventListener('load',onLoad,{once:true})}if(document.fonts&&document.fonts.ready){document.fonts.ready.then(function(){fontsDone=true;maybe()}).catch(function(){fontsDone=true;maybe()})}else{fontsDone=true}setTimeout(function(){loadDone=true;fontsDone=true;maybe()},3500)})();`;

export function CriticalPaintGuard() {
  return (
    <>
      <style dangerouslySetInnerHTML={{ __html: CRITICAL_CSS }} />
      <script dangerouslySetInnerHTML={{ __html: BOOT_SCRIPT }} />
    </>
  );
}
