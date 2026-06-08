(function(){const t=document.createElement("link").relList;if(t&&t.supports&&t.supports("modulepreload"))return;for(const a of document.querySelectorAll('link[rel="modulepreload"]'))o(a);new MutationObserver(a=>{for(const s of a)if(s.type==="childList")for(const c of s.addedNodes)c.tagName==="LINK"&&c.rel==="modulepreload"&&o(c)}).observe(document,{childList:!0,subtree:!0});function n(a){const s={};return a.integrity&&(s.integrity=a.integrity),a.referrerPolicy&&(s.referrerPolicy=a.referrerPolicy),a.crossOrigin==="use-credentials"?s.credentials="include":a.crossOrigin==="anonymous"?s.credentials="omit":s.credentials="same-origin",s}function o(a){if(a.ep)return;a.ep=!0;const s=n(a);fetch(a.href,s)}})();const r="http://localhost:4000/api";async function i(e){const n=e.headers.get("content-type")?.includes("application/json")?await e.json():null;if(!e.ok)throw new Error(n?.message||"Request failed");return n}async function d(){const e=await fetch(`${r}/categories`);return(await i(e)).data}async function l(e=""){const t=new URL(`${r}/news`);e&&t.searchParams.set("category",e);const n=await fetch(t);return(await i(n)).data}async function f(e){const t=await fetch(`${r}/news/${e}`);return(await i(t)).data}async function u(e){const t=await fetch(`${r}/news`,{method:"POST",body:e});return(await i(t)).data}function h(e){return e?new Intl.DateTimeFormat("vi-VN",{day:"2-digit",month:"2-digit",year:"numeric"}).format(new Date(e)):""}function p({title:e,subtitle:t="",actions:n="",content:o}){return`
    <div class="site-shell">
      <header class="hero">
        <div class="hero__backdrop"></div>
        <div class="hero__inner">
          <div class="hero__eyebrow">UBND Long Hiep</div>
          <div class="hero__copy">
            <h1>${e}</h1>
            <p>${t}</p>
          </div>
          <div class="hero__actions">
            <a class="ghost-button" href="/index.html">Tin tuc</a>
            <a class="ghost-button" href="/admin-news-new.html">Dang bai</a>
            ${n}
          </div>
        </div>
      </header>
      <main class="page">${o}</main>
    </div>
  `}function y(e,t="info"){return`<div class="message message--${t}">${e}</div>`}export{l as a,y as b,h as c,f as d,u as e,d as f,p as r};
