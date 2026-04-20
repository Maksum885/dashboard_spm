<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>SPM Oil & Gas</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
<link href="https://fonts.googleapis.com/css2?family=Titillium+Web:wght@400;600;700;900&family=Barlow+Condensed:wght@400;500;600;700&family=Barlow:wght@400;500;600&display=swap" rel="stylesheet">
<style>
:root{
  --bg:#d0dae8;
  --s1:#ffffff;
  --s2:#f1f5f9;
  --s3:#e8edf4;
  --b1:#d1dbe8;
  --b2:#bfcbda;
  --b3:#a8baca;
  --txt:#0f172a;
  --dim:#0d1820;
  --muted:#1a2530;
  --white:#fff;
  --ok:#059652;
  --ok2:rgba(5,150,82,.10);
  --ok3:rgba(5,150,82,.32);
  --wa:#c27a00;
  --wa2:rgba(194,122,0,.10);
  --wa3:rgba(194,122,0,.32);
  --cr:#d92020;
  --cr2:rgba(217,32,32,.08);
  --cr3:rgba(217,32,32,.28);
  --in:#1564c0;
  --in2:rgba(21,100,192,.08);
  --in3:rgba(21,100,192,.28);
  --amber:#d4720a;
  --F:'Barlow',sans-serif;
  --FC:'Barlow Condensed',sans-serif;
  --M:'Titillium Web',sans-serif;
  --LW:252px; --RW:330px; --HDR:52px;
}
*{margin:0;padding:0;box-sizing:border-box}
html,body{width:100%;height:100%;overflow:hidden;background:var(--bg);color:var(--txt);font-family:var(--F)}
#cv{position:fixed;inset:0}

/* ── HEADER ── */
.hdr{
  position:fixed;top:0;left:0;right:0;height:var(--HDR);z-index:700;
  background:rgba(255,255,255,.97);
  border-bottom:2px solid var(--amber);
  box-shadow:0 1px 8px rgba(0,0,0,.10);
  display:flex;align-items:center;padding:0 15px;gap:12px
}
.logo{display:flex;align-items:center;gap:8px;flex-shrink:0}
.logo-img{height:30px;width:auto;object-fit:contain}
.logo-fallback{font-family:var(--FC);font-size:13px;font-weight:700;color:var(--amber);letter-spacing:2px;display:none}
.hdr-sep{width:1px;height:26px;background:var(--b1);flex-shrink:0}
.hdr-kpi{display:flex;align-items:center;gap:4px;margin-left:16px}
.hdr-kpi-item{display:flex;align-items:center;gap:5px;padding:3px 10px;border-radius:3px;background:var(--s2);border:1px solid var(--b1)}
.hdr-kpi-lbl{font-family:var(--M);font-size:8px;color:var(--muted);letter-spacing:1.5px}
.hdr-kpi-val{font-family:var(--M);font-size:11px;font-weight:600;color:var(--txt)}
.hdr-kpi-dot{width:5px;height:5px;border-radius:50%}
.hdr-r{display:flex;align-items:center;gap:8px;margin-left:auto}
@keyframes dp{0%,100%{opacity:1}50%{opacity:.2}}
.clk{font-family:var(--M);font-size:16px;font-weight:600;color:var(--txt);letter-spacing:2px}
.ham{display:none;background:none;border:1px solid var(--b2);color:var(--dim);
  border-radius:3px;width:30px;height:30px;cursor:pointer;
  align-items:center;justify-content:center;font-size:18px}

/* ── LEFT SIDEBAR ── */
.lsb{
  position:fixed;top:var(--HDR);left:0;bottom:0;width:var(--LW);
  background:var(--s1);border-right:1px solid var(--b1);
  box-shadow:2px 0 8px rgba(0,0,0,.04);
  display:flex;flex-direction:column;z-index:500;overflow:hidden;
  transition:transform .28s cubic-bezier(.4,0,.2,1)
}
.lscroll{flex:1;overflow-y:auto;overflow-x:hidden}
.lscroll::-webkit-scrollbar{width:3px}
.lscroll::-webkit-scrollbar-thumb{background:var(--b2);border-radius:2px}
.blk{border-bottom:1px solid var(--b1)}
.bh{display:flex;align-items:center;justify-content:space-between;padding:10px 14px}
.bt{font-family:var(--M);font-size:11px;letter-spacing:1.5px;color:var(--dim);
  display:flex;align-items:center;gap:6px;text-transform:uppercase;font-weight:600}
.bt i{font-size:14px;color:var(--dim)}
.bc{font-family:var(--M);font-size:10px;padding:2px 7px;border-radius:2px;letter-spacing:1px;font-weight:600}
.bc.ok{background:var(--ok2);color:var(--ok);border:1px solid var(--ok3)}
.bc.wa{background:var(--wa2);color:var(--wa);border:1px solid var(--wa3)}
.bc.cr{background:var(--cr2);color:var(--cr);border:1px solid var(--cr3)}
.bc.in{background:var(--in2);color:var(--in);border:1px solid var(--in3)}
.bb{padding:6px 12px 10px}
.cr-item{
  display:flex;align-items:center;gap:9px;padding:10px 14px;
  cursor:pointer;border-left:3px solid transparent;
  transition:all .15s;border-bottom:1px solid var(--b1)
}
.cr-item:last-child{border-bottom:none}
.cr-item:hover{background:var(--s2)}
.cr-item.active{background:var(--in2);border-left-color:var(--ic,var(--in))}
.cr-info{flex:1;min-width:0}
.cr-name{font-family:var(--FC);font-size:13px;font-weight:700;letter-spacing:.5px;color:var(--txt)}
.cr-sub{font-family:var(--F);font-size:10.5px;color:var(--txt);margin-top:2px;
  white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.cr-badge{font-family:var(--M);font-size:9px;padding:2px 7px;border-radius:2px;flex-shrink:0;font-weight:600}
.cr-badge.ok{background:var(--ok2);color:var(--ok);border:1px solid var(--ok3)}
.cr-badge.wa{background:var(--wa2);color:var(--wa);border:1px solid var(--wa3)}
.cr-badge.cr{background:var(--cr2);color:var(--cr);border:1px solid var(--cr3)}

/* ── CR EXPAND / ROOM LIST ── */
.cr-expand-btn{background:none;border:none;cursor:pointer;padding:0;
  display:flex;align-items:center;justify-content:center;
  width:22px;height:22px;border-radius:3px;color:var(--dim);
  transition:all .15s;flex-shrink:0}
.cr-expand-btn:hover{background:var(--b1);color:var(--txt)}
.cr-expand-btn i{font-size:13px;transition:transform .2s}
.cr-expand-btn.open i{transform:rotate(90deg)}
.cr-rooms{overflow:hidden;max-height:0;transition:max-height .28s ease}
.cr-rooms.open{max-height:600px}
.room-item{
  display:flex;align-items:center;gap:8px;
  padding:7px 14px 7px 26px;
  cursor:pointer;border-left:3px solid transparent;
  border-bottom:1px solid var(--b1);transition:all .12s;
  background:var(--s2)
}
.room-item:last-child{border-bottom:none}
.room-item:hover{background:var(--s3);border-left-color:var(--b2)}
.room-item.active{background:rgba(5,150,82,.07);border-left-color:var(--ok)}
.room-dot{width:6px;height:6px;border-radius:1px;flex-shrink:0}
.room-nm{font-family:var(--FC);font-size:12px;font-weight:600;color:var(--dim);flex:1}
.room-item.active .room-nm{color:var(--txt)}
.room-st{font-family:var(--M);font-size:8px;padding:1px 5px;border-radius:2px;flex-shrink:0;font-weight:600}
.room-st.ok{background:var(--ok2);color:var(--ok);border:1px solid var(--ok3)}
.room-st.wa{background:var(--wa2);color:var(--wa);border:1px solid var(--wa3)}
.room-st.cr{background:var(--cr2);color:var(--cr);border:1px solid var(--cr3)}
.room-st.sb{background:var(--in2);color:var(--in);border:1px solid var(--in3)}

/* ── BREADCRUMB / BACK ── */
.rp-breadcrumb{
  display:flex;align-items:center;gap:5px;margin-bottom:4px;
  font-family:var(--M);font-size:8px;color:var(--muted);letter-spacing:1px
}
.rp-breadcrumb .bc-link{cursor:pointer;color:var(--in);transition:color .12s}
.rp-breadcrumb .bc-link:hover{color:var(--txt);text-decoration:underline}
.rp-breadcrumb .bc-sep{color:var(--b3)}
.rp-back{
  display:inline-flex;align-items:center;gap:5px;
  background:var(--s1);border:1px solid var(--b2);border-radius:3px;
  padding:5px 10px;cursor:pointer;color:var(--dim);
  font-family:var(--M);font-size:10px;letter-spacing:1px;font-weight:600;
  transition:all .15s;margin-bottom:12px
}
.rp-back:hover{background:var(--s3);color:var(--txt);border-color:var(--b3)}
.rp-back i{font-size:11px}

/* ── ROOM DETAIL GRID ── */
.rd-env{background:var(--s1);border:1px solid var(--b1);border-radius:5px;margin-bottom:8px;overflow:hidden}
.rd-env-hdr{display:flex;align-items:center;gap:7px;padding:8px 12px;background:var(--s2);border-bottom:1px solid var(--b1)}
.rd-env-title{font-family:var(--M);font-size:9px;letter-spacing:2px;color:var(--dim);font-weight:600;flex:1}
.rd-env-body{padding:10px 12px}
.rd-grid{display:grid;grid-template-columns:1fr 1fr 1fr;gap:5px;margin-bottom:0}
.rd-grid2{display:grid;grid-template-columns:1fr 1fr;gap:5px;margin-bottom:0}
.rd-cell{background:var(--s2);border-radius:3px;padding:6px 8px}
.rd-cell-lbl{font-family:var(--M);font-size:10px;color:var(--muted);margin-bottom:2px;font-weight:500}
.rd-cell-val{font-family:var(--M);font-size:14px;font-weight:700;color:var(--txt);line-height:1.1}
.rd-cell-val.wa{color:var(--wa)}.rd-cell-val.cr{color:var(--cr)}
.rd-cell-val.ok{color:var(--ok)}.rd-cell-val.in{color:var(--in)}.rd-cell-val.amb{color:var(--amber)}

/* ── ALARM LIST ── */
.al-row{display:flex;align-items:flex-start;gap:7px;padding:7px 4px;border-bottom:1px solid var(--b1);cursor:pointer;border-radius:2px;transition:background .12s}
.al-row:hover{background:var(--s2)}
.al-row:last-child{border:none}
.al-bar{width:3px;border-radius:2px;flex-shrink:0;align-self:stretch;min-height:26px}
.al-bar.cr{background:var(--cr)}.al-bar.wa{background:var(--wa)}
.al-txt{flex:1;min-width:0}
.al-name{font-size:11px;font-weight:600;color:var(--txt);line-height:1.25}
.al-sub{font-family:var(--M);font-size:10px;color:var(--dim);margin-top:2px}
.al-time{font-family:var(--M);font-size:12px;letter-spacing:.5px;color:var(--dim);flex-shrink:0}

/* ── UTILITAS SIDEBAR ── */
.util-row{display:flex;align-items:center;gap:8px;padding:7px 14px;border-bottom:1px solid var(--b1);cursor:pointer;transition:background .12s}
.util-row:hover{background:var(--s2)}
.util-row:last-child{border:none}
.util-ico{width:26px;height:26px;border-radius:4px;display:grid;place-items:center;font-size:14px;flex-shrink:0}
.util-info{flex:1;min-width:0}
.util-name{font-family:var(--FC);font-size:12px;font-weight:700;color:var(--txt)}
.util-val{font-family:var(--M);font-size:11px;color:var(--txt);margin-top:1px;font-weight:600}
.util-st{font-family:var(--M);font-size:8px;padding:2px 6px;border-radius:2px;flex-shrink:0;font-weight:600}
.util-st.ok{background:var(--ok2);color:var(--ok);border:1px solid var(--ok3)}
.util-st.wa{background:var(--wa2);color:var(--wa);border:1px solid var(--wa3)}
.util-st.cr{background:var(--cr2);color:var(--cr);border:1px solid var(--cr3)}
.util-st.sb{background:var(--in2);color:var(--in);border:1px solid var(--in3)}

/* ── LABELS 3D ── */
.lyr{position:fixed;inset:0;pointer-events:none;z-index:65}
.lbl{position:absolute;transform:translate(-50%,-50%);text-align:center;white-space:nowrap;pointer-events:none;line-height:1.35}
.lbl-ext{position:absolute;pointer-events:auto;cursor:pointer;white-space:nowrap;line-height:1.4}
.lbl-ext-name{font-family:var(--M);font-size:9px;font-weight:600;color:#1a2a3a;letter-spacing:.8px;text-transform:uppercase}
#lyr-svg{position:fixed;inset:0;pointer-events:none;z-index:64}

/* ── TOOLTIP ── */
#tip{
  position:fixed;pointer-events:none;z-index:900;
  background:rgba(255,255,255,.97);border:1px solid var(--b2);border-top:2px solid;
  border-radius:0 0 4px 4px;padding:5px 13px;
  font-family:var(--M);font-size:8px;
  box-shadow:0 4px 16px rgba(0,0,0,.14);
  opacity:0;transition:opacity .1s;white-space:nowrap
}

/* ── RIGHT PANEL ── */
.rpanel{
  position:fixed;top:var(--HDR);right:0;bottom:0;width:var(--RW);
  background:var(--s1);border-left:2px solid var(--amber);
  box-shadow:-4px 0 20px rgba(0,0,0,.10);
  display:flex;flex-direction:column;z-index:600;
  transform:translateX(var(--RW));transition:transform .28s cubic-bezier(.4,0,.2,1)
}
.rpanel.open{transform:translateX(0)}
.rp-accent{display:none}
.rp-hdr{
  padding:14px 16px 12px;flex-shrink:0;
  background:var(--s1);
  border-bottom:2px solid var(--b1)
}
.rp-hdr-row{display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:6px}
.rp-type{font-family:var(--M);font-size:10px;color:var(--muted);letter-spacing:3px;margin-bottom:3px;text-transform:uppercase}
.rp-name{font-family:var(--FC);font-size:20px;font-weight:700;letter-spacing:.5px;color:var(--txt)}
.rp-zone{font-family:var(--M);font-size:10px;color:var(--dim);letter-spacing:1px;margin-top:3px;font-weight:500}
.rp-close{
  background:var(--s2);border:1px solid var(--b2);color:var(--dim);
  width:28px;height:28px;border-radius:4px;cursor:pointer;
  display:grid;place-items:center;font-size:15px;transition:all .15s;flex-shrink:0
}
.rp-close:hover{background:var(--cr2);border-color:var(--cr);color:var(--cr)}
.rp-breadcrumb{
  display:flex;align-items:center;gap:5px;margin-bottom:4px;
  font-family:var(--M);font-size:9px;color:var(--muted);letter-spacing:1px
}
.rp-breadcrumb .bc-link{cursor:pointer;color:var(--in);transition:color .12s}
.rp-breadcrumb .bc-link:hover{color:var(--txt)}
.rp-breadcrumb .bc-sep{color:var(--b3)}

/* ── TABS ── */
.rp-tabs{display:flex;flex-shrink:0;background:var(--s2);overflow-x:auto;border-bottom:2px solid var(--b1)}
.rp-tabs::-webkit-scrollbar{height:0}
.rp-tab{
  flex:1;min-width:48px;padding:9px 3px;text-align:center;cursor:pointer;
  font-family:var(--M);font-size:10px;letter-spacing:1px;color:var(--dim);
  border-bottom:2px solid transparent;margin-bottom:-2px;
  transition:all .15s;font-weight:600;white-space:nowrap
}
.rp-tab.on{color:var(--txt);border-bottom-color:var(--amber);background:var(--s1)}
.rp-tab:hover:not(.on){background:var(--s3);color:var(--txt)}

/* ── PANEL BODY ── */
.rp-body{flex:1;overflow-y:auto;padding:14px 16px;background:var(--s2)}
.rp-body::-webkit-scrollbar{width:3px}
.rp-body::-webkit-scrollbar-thumb{background:var(--b2);border-radius:2px}
.rp-sec{
  font-family:var(--M);font-size:10px;letter-spacing:2px;color:var(--dim);
  display:flex;align-items:center;gap:7px;margin:14px 0 9px;text-transform:uppercase;font-weight:700
}
.rp-sec:first-child{margin-top:0}
.rp-sec::after{content:'';flex:1;height:1px;background:var(--b1)}

/* ── METRIC CARDS ── */
.mc-grid{display:grid;grid-template-columns:1fr 1fr;gap:7px}
.mc-grid3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:7px}
.mc{background:var(--s1);border:1px solid var(--b1);border-radius:5px;padding:11px;
  position:relative;overflow:hidden;box-shadow:0 1px 4px rgba(0,0,0,.05)}
.mc::before{content:'';position:absolute;top:0;left:0;right:0;height:3px;background:var(--mc,var(--in))}
.mc.T{--mc:#e05a10}.mc.H{--mc:#0868b0}.mc.P{--mc:#7820c0}.mc.W{--mc:#c07810}
.mc.FL{--mc:#0d9488}.mc.PR{--mc:#7c3aed}.mc.MX{--mc:#b45309}.mc.LV{--mc:#10904a}
.mc.PM{--mc:#0369a1}.mc.CY{--mc:#1880b0}.mc.EG{--mc:#16a34a}
.mc-lbl{font-family:var(--M);font-size:11px;color:var(--muted);letter-spacing:1.5px;margin-bottom:4px;font-weight:600}
.mc-val{font-family:var(--M);font-size:20px;font-weight:700;color:var(--mc);line-height:1}
.mc-val.sm{font-size:16px}
.mc-st{display:inline-flex;align-items:center;gap:3px;margin-top:5px;
  font-family:var(--M);font-size:11px;padding:2px 8px;border-radius:2px;font-weight:600}
.mc-st::before{content:'';width:4px;height:4px;border-radius:50%;background:currentColor;flex-shrink:0}
.mc-st.ok{background:var(--ok2);color:var(--ok);border:1px solid var(--ok3)}
.mc-st.wa{background:var(--wa2);color:var(--wa);border:1px solid var(--wa3)}
.mc-st.cr{background:var(--cr2);color:var(--cr);border:1px solid var(--cr3)}
.mc-st.sb{background:var(--in2);color:var(--in);border:1px solid var(--in3)}

/* ── PROGRESS BAR ── */
.pbar-wrap{margin-top:6px}
.pbar-row{display:flex;align-items:center;gap:5px;margin-bottom:4px}
.pbar-lbl{font-family:var(--M);font-size:8px;color:var(--dim);width:18px}
.pbar{flex:1;height:3px;background:var(--b1);border-radius:2px;overflow:hidden}
.pbar-fill{height:100%;border-radius:2px;transition:width 1s}
.pbar-val{font-family:var(--M);font-size:8px;color:var(--dim);width:30px;text-align:right}

/* ── PHASE INDICATOR ── */
.phase-wrap{display:flex;gap:3px;margin-top:8px}
.phase-step{flex:1;height:4px;border-radius:2px;background:var(--b1)}
.phase-step.done{background:var(--ok)}
.phase-step.active{background:var(--amber)}
.phase-lbl{font-family:var(--M);font-size:8px;color:var(--dim);margin-top:4px;text-align:center}

/* ── LOCK ITEMS ── */
.lk-item{display:flex;align-items:center;gap:8px;padding:8px 11px;
  background:var(--s1);border:1px solid var(--b1);border-radius:4px;margin-bottom:5px;
  box-shadow:0 1px 3px rgba(0,0,0,.04)}
.lk-item:last-child{margin-bottom:0}
.lk-ico{font-size:16px;flex-shrink:0}
.lk-name{font-size:12px;font-weight:500;flex:1;color:var(--txt)}
.lk-time{font-family:var(--M);font-size:9px;color:var(--dim)}
.lk-badge{font-family:var(--M);font-size:8px;padding:2px 7px;border-radius:2px;flex-shrink:0;font-weight:600}
.lk-badge.lk{background:var(--ok2);color:var(--ok);border:1px solid var(--ok3)}
.lk-badge.ul{background:var(--cr2);color:var(--cr);border:1px solid var(--cr3)}

/* ── ROOM ROWS ── */
.rm-row{display:flex;align-items:center;gap:8px;padding:8px 11px;
  background:var(--s1);border:1px solid var(--b1);border-radius:4px;margin-bottom:5px;
  cursor:pointer;transition:all .12s;box-shadow:0 1px 3px rgba(0,0,0,.04)}
.rm-row:last-child{margin-bottom:0}
.rm-row:hover{background:var(--s2);border-color:var(--b2);box-shadow:0 2px 8px rgba(0,0,0,.08)}
.rm-dot{width:7px;height:7px;border-radius:2px;flex-shrink:0}
.rm-info{flex:1;min-width:0}
.rm-name{font-size:12px;font-weight:600;color:var(--txt)}
.rm-type{font-family:var(--M);font-size:9px;color:var(--dim);margin-top:1px;letter-spacing:1px}
.rm-vals{display:flex;gap:6px;align-items:center}
.rm-val{font-family:var(--M);font-size:9px;color:var(--dim)}
.rm-st{font-family:var(--M);font-size:9px;padding:2px 6px;border-radius:2px;flex-shrink:0;font-weight:600}
.rm-st.ok{background:var(--ok2);color:var(--ok);border:1px solid var(--ok3)}
.rm-st.wa{background:var(--wa2);color:var(--wa);border:1px solid var(--wa3)}
.rm-st.cr{background:var(--cr2);color:var(--cr);border:1px solid var(--cr3)}
.rm-st.sb{background:var(--in2);color:var(--in);border:1px solid var(--in3)}

/* ── EVENT LOG ── */
.ev-row{display:flex;align-items:flex-start;gap:8px;padding:7px 0;border-bottom:1px solid var(--b1)}
.ev-row:last-child{border:none}
.ev-t{font-family:var(--M);font-size:9px;color:var(--muted);width:34px;flex-shrink:0;padding-top:2px}
.ev-d{width:6px;height:6px;border-radius:50%;flex-shrink:0;margin-top:4px}
.ev-d.ok{background:var(--ok)}.ev-d.wa{background:var(--wa)}
.ev-d.cr{background:var(--cr)}.ev-d.in{background:var(--in)}
.ev-m{font-size:11px;color:var(--txt);line-height:1.4}

/* ── UTIL PANEL CARDS ── */
.util-card{background:var(--s1);border:1px solid var(--b1);border-radius:5px;margin-bottom:8px;overflow:hidden}
.util-card-hdr{display:flex;align-items:center;gap:8px;padding:9px 12px;background:var(--s2);border-bottom:1px solid var(--b1)}
.util-card-ico{font-size:15px}
.util-card-title{font-family:var(--FC);font-size:13px;font-weight:700;flex:1;color:var(--txt)}
.util-card-body{padding:10px 12px}
.util-data-row{display:flex;align-items:center;justify-content:space-between;margin-bottom:6px}
.util-data-row:last-child{margin-bottom:0}
.util-data-lbl{font-family:var(--M);font-size:11px;color:var(--dim);letter-spacing:.5px}
.util-data-val{font-family:var(--M);font-size:13px;font-weight:700;color:var(--txt)}
.util-data-val.ok{color:var(--ok)}.util-data-val.wa{color:var(--wa)}.util-data-val.cr{color:var(--cr)}

/* ── LEVEL BAR ── */
.level-bar-wrap{display:flex;align-items:center;gap:8px;margin-top:6px}
.level-bar-outer{flex:1;height:8px;background:var(--b1);border-radius:4px;overflow:hidden}
.level-bar-inner{height:100%;border-radius:4px;transition:width 1s}
.level-bar-pct{font-family:var(--M);font-size:9px;font-weight:600;width:28px;text-align:right}

/* ── TOAST ── */
.toast{position:fixed;bottom:16px;left:50%;transform:translateX(-50%) translateY(60px);
  background:var(--s1);border:1px solid var(--b2);border-top:2px solid var(--in);
  border-radius:0 0 4px 4px;padding:6px 20px;font-family:var(--M);font-size:8px;
  color:var(--in);z-index:9998;white-space:nowrap;opacity:0;transition:all .28s;
  box-shadow:0 4px 16px rgba(0,0,0,.12)}
.toast.show{transform:translateX(-50%) translateY(0);opacity:1}

/* ── OVERLAY ── */
.overlay{position:fixed;inset:0;background:rgba(0,0,0,.35);z-index:499;display:none;opacity:0;transition:opacity .28s}

@media(max-width:768px){
  :root{--HDR:48px}
  .lsb{width:270px;transform:translateX(-270px)}
  .lsb.open{transform:translateX(0)}
  .rpanel{width:100vw;--RW:100vw}
  .ham{display:flex}
  .clk{font-size:14px}
  .hdr-kpi{display:none}
}
</style>
</head>
<body>
<canvas id="cv"></canvas>

<header class="hdr">
  <div class="logo">
    <img src="{{ asset('images/logospm1.png') }}" alt="SPM" class="logo-img"
        onerror="this.style.display='none';document.querySelector('.logo-fallback').style.display='block'">
    <span class="logo-fallback">SPM OIL &amp; GAS</span>
  </div>
  <div class="hdr-r">
    <div class="clk" id="clk">00:00:00</div>
    <button class="ham" onclick="toggleSidebar()"><i class="ti ti-menu-2"></i></button>
  </div>
</header>

<aside class="lsb" id="lsb">
  <div class="lscroll">
    <!-- Control Rooms -->
    <div class="blk">
      <div class="bh">
        <div class="bt"><i class="ti ti-building"></i>CONTROL ROOMS</div>
      </div>
      <div style="padding:0">
        <!-- CR1 -->
        <div class="cr-item active" id="nav-cr1" style="--ic:#1564c0">
          <div class="cr-info" onclick="openPanel('cr1')" style="cursor:pointer">
            <div class="cr-name">Control Room 1</div>
            <div class="cr-sub">Cell 1 · Cell 2 · Cell 3</div>
          </div>
          <span class="cr-badge wa" id="nb-cr1">WARN</span>
          <button class="cr-expand-btn" id="exp-cr1" onclick="toggleRooms('cr1')"><i class="ti ti-chevron-right"></i></button>
        </div>
        <div class="cr-rooms" id="rooms-cr1"></div>
        <!-- CR2 -->
        <div class="cr-item" id="nav-cr2" style="--ic:#1564c0">
          <div class="cr-info" onclick="openPanel('cr2')" style="cursor:pointer">
            <div class="cr-name">Control Room 2</div>
            <div class="cr-sub">Cell 4 · Cell 5 · Pit 1 · Pit 2</div>
          </div>
          <span class="cr-badge cr" id="nb-cr2">ALARM</span>
          <button class="cr-expand-btn" id="exp-cr2" onclick="toggleRooms('cr2')"><i class="ti ti-chevron-right"></i></button>
        </div>
        <div class="cr-rooms" id="rooms-cr2"></div>
        <!-- CR3 -->
        <div class="cr-item" id="nav-cr3" style="--ic:#1564c0">
          <div class="cr-info" onclick="openPanel('cr3')" style="cursor:pointer">
            <div class="cr-name">Control Room 3</div>
            <div class="cr-sub">Pit 3 · Pit 4 · Pit 5 · Pit 6</div>
          </div>
          <span class="cr-badge ok" id="nb-cr3">OK</span>
          <button class="cr-expand-btn" id="exp-cr3" onclick="toggleRooms('cr3')"><i class="ti ti-chevron-right"></i></button>
        </div>
        <div class="cr-rooms" id="rooms-cr3"></div>
      </div>
    </div>
    <!-- Alarm -->
    <div class="blk">
      <div class="bh">
        <div class="bt"><i class="ti ti-bell-ringing"></i>ALARM AKTIF</div>
        <span class="bc cr" id="al-count">0</span>
      </div>
      <div class="bb" id="al-list">
        <div style="font-family:var(--M);font-size:12px;color:var(--txt);padding:4px 0">Tidak ada alarm aktif</div>
      </div>
    </div>
    <!-- Utilitas -->
    <div class="blk">
      <div class="bh">
        <div class="bt"><i class="ti ti-tool"></i>UTILITAS</div>
      </div>
      <div onclick="openUtilPanel()" style="cursor:pointer">
        <div class="util-row">
          <div class="util-ico" style="background:var(--in2);color:var(--in)"><i class="ti ti-droplet"></i></div>
          <div class="util-info">
            <div class="util-name">Motor PAM</div>
            <div class="util-val" id="sb-pam-val">125.4 L/min · 4.8 bar</div>
          </div>
          <div class="util-st ok" id="sb-pam-st">RUN</div>
        </div>
        <div class="util-row">
          <div class="util-ico" style="background:var(--ok2);color:var(--ok)"><i class="ti ti-bolt"></i></div>
          <div class="util-info">
            <div class="util-name">Listrik</div>
            <div class="util-val" id="sb-pwr-val">284.2 kW · PF 0.92</div>
          </div>
          <div class="util-st ok" id="sb-pwr-st">NORMAL</div>
        </div>
        <div class="util-row">
          <div class="util-ico" style="background:var(--wa2);color:var(--wa)"><i class="ti ti-air-conditioning"></i></div>
          <div class="util-info">
            <div class="util-name">HVAC</div>
            <div class="util-val" id="sb-hvac-val">Set 22°C · Actual 23.1°C</div>
          </div>
          <div class="util-st ok" id="sb-hvac-st">OK</div>
        </div>
      </div>
    </div>
  </div>
</aside>

<svg id="lyr-svg"></svg>
<div class="lyr" id="lyr"></div>
<div id="tip"></div>
<div class="overlay" id="overlay" onclick="closeSidebar()"></div>

<!-- RIGHT PANEL -->
<div class="rpanel" id="rpanel">
  <div id="camera-view" style="
  width:100%;
  height:260px;
  background:#000;
  display:none;
  justify-content:center;
  align-items:center;
  border-radius:10px;
  overflow:hidden;
">
      <video id="camera" autoplay playsinline style="width:100%;height:100%;object-fit:cover;"></video>
  </div>
  <div class="rp-accent" id="rp-accent"></div>
  <div class="rp-hdr">
    <div class="rp-hdr-row">
      <div>
        <div class="rp-breadcrumb" id="rp-breadcrumb" style="display:none"></div>
        <div class="rp-type" id="rp-type">—</div>
        <div class="rp-name" id="rp-name">—</div>
        <div class="rp-zone" id="rp-zone">—</div>
      </div>
      <button class="rp-close" onclick="closePanel()"><i class="ti ti-x"></i></button>
    </div>
  <div class="rp-tabs" id="rp-tabs">
    <div class="rp-tab on" id="tab-s" onclick="swTab('s')">SENSOR</div>
    <div class="rp-tab" id="tab-l" onclick="swTab('l')">LOCK</div>
    <div class="rp-tab" id="tab-r" onclick="swTab('r')">RUANGAN</div>
    <div class="rp-tab" id="tab-e" onclick="swTab('e')">LOG</div>
    <div class="rp-tab" id="tab-u" onclick="swTab('u')" style="display:none">UTILITAS</div>
    <div class="rp-tab" id="tab-m" onclick="swTab('m')" style="display:none">MESIN</div>
    <div class="rp-tab" id="tab-p" onclick="swTab('p')" style="display:none">PRESSURE</div>
  </div>
  <div class="rp-body">
    <div id="pane-s"></div>
    <div id="pane-l" style="display:none"></div>
    <div id="pane-r" style="display:none"></div>
    <div id="pane-e" style="display:none"></div>
    <div id="pane-u" style="display:none"></div>
    <div id="pane-m" style="display:none"></div>
    <div id="pane-p" style="display:none"></div>
  </div>
</div>

<div class="toast" id="toast"></div>
<!-- Hidden KPI elements for JS compatibility -->
<span id="hkpi-alarm" style="display:none"></span>
<span id="hkpi-alarm-dot" style="display:none"></span>
<span id="hkpi-active" style="display:none"></span>
<span id="hkpi-pwr" style="display:none"></span>

<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
<script>
/* ══════════════════════════════════════════
   DATA — LENGKAP SESUAI PEMBAHASAN
   (Front-end only / simulasi)
══════════════════════════════════════════ */
const DATA = {
  cr1:{
    tp:'CONTROL ROOM', nm:'Control Room 1', zn:'TEST AREA · SECTOR A', col:'#1564c0',
    // Sensor CR
    temp:24.3, hum:58, pwr:87.2, volt:218.9, freq:50.0, pf:0.91, st:'WARNING',
    locks:[
      {name:'Main Door',     st:'locked',   time:'14:28'},
      {name:'Server Rack',   st:'locked',   time:'09:00'},
      {name:'Fire Exit',     st:'unlocked', time:'08:30'},
      {name:'Cabinet A',     st:'locked',   time:'09:00'}
    ],
    events:[
      {t:'14:32',c:'wa',m:'Temperature elevated 24.3°C'},
      {t:'14:28',c:'ok',m:'Access verification OK'},
      {t:'13:58',c:'ok',m:'Power stable 87.2%'},
      {t:'13:30',c:'in',m:'Backup sync complete'}
    ],
    rooms:[
      {
        id:'cell1', tp:'TEST CELL', nm:'Test Cell 1', col:'#059652', st:'ACTIVE',
        // Sensor ruangan
        temp:22.8, hum:54, pres:1014.0,
        // Mesin yang diuji
        rpm:1450, torque:185.4, power_out:28.1,
        temp_oli:68.2, temp_coolant:82.4,
        pres_oli:4.2, pres_bbm:6.8,
        flow_bbm:12.4, temp_exhaust:385,
        // Utilitas ruangan
        flow_coolant:85.2, pres_coolant:2.8,
        konsumsi_listrik:28.1,
        // Test status
        load:75.5, cycle:'3/4',
        test_id:'TC1-2024-089', test_dur:'02:14',
        phase:'HOLDING', phase_step:3
      },
      {
        id:'cell2', tp:'TEST CELL', nm:'Test Cell 2', col:'#059652', st:'ACTIVE',
        temp:22.3, hum:53, pres:1014.2,
        rpm:1380, torque:162.8, power_out:23.5,
        temp_oli:65.8, temp_coolant:78.9,
        pres_oli:4.0, pres_bbm:6.5,
        flow_bbm:10.8, temp_exhaust:362,
        flow_coolant:78.4, pres_coolant:2.6,
        konsumsi_listrik:23.5,
        load:72.8, cycle:'2/3',
        test_id:'TC2-2024-088', test_dur:'01:45',
        phase:'RUNNING', phase_step:2
      },
      {
        id:'cell3', tp:'TEST CELL', nm:'Test Cell 3', col:'#059652', st:'ACTIVE',
        temp:21.9, hum:53, pres:1014.3,
        rpm:1320, torque:148.2, power_out:20.4,
        temp_oli:63.1, temp_coolant:75.2,
        pres_oli:3.9, pres_bbm:6.2,
        flow_bbm:9.6, temp_exhaust:348,
        flow_coolant:72.8, pres_coolant:2.5,
        konsumsi_listrik:20.4,
        load:70.2, cycle:'1/3',
        test_id:'TC3-2024-087', test_dur:'00:52',
        phase:'RUNNING', phase_step:2
      }
    ]
  },
  cr2:{
    tp:'CONTROL ROOM', nm:'Control Room 2', zn:'TEST AREA · SECTOR B', col:'#1564c0',
    temp:26.8, hum:62, pwr:91.5, volt:217.2, freq:50.1, pf:0.89, st:'WARNING',
    locks:[
      {name:'Main Door',   st:'unlocked', time:'13:42'},
      {name:'Server Rack', st:'locked',   time:'09:00'},
      {name:'Fire Exit',   st:'unlocked', time:'08:30'},
      {name:'Cabinet B',   st:'locked',   time:'09:00'}
    ],
    events:[
      {t:'14:32',c:'cr',m:'TEMP EXCEEDED 26.8°C — limit 26.0°C'},
      {t:'13:42',c:'wa',m:'Main door unlocked by tech'},
      {t:'13:15',c:'in',m:'Humidity 62% monitoring'},
      {t:'12:50',c:'wa',m:'Power load high 91.5%'}
    ],
    rooms:[
      {
        id:'cell4', tp:'TEST CELL', nm:'Test Cell 4', col:'#d92020', st:'ACTIVE',
        temp:24.8, hum:58, pres:1013.2,
        rpm:1580, torque:210.4, power_out:34.8,
        temp_oli:72.4, temp_coolant:88.2,
        pres_oli:4.5, pres_bbm:7.2,
        flow_bbm:14.8, temp_exhaust:412,
        flow_coolant:94.2, pres_coolant:3.1,
        konsumsi_listrik:34.8,
        load:82.1, cycle:'2/4',
        test_id:'TC4-2024-091', test_dur:'03:28',
        phase:'PRESSURIZING', phase_step:2
      },
      {
        id:'cell5', tp:'TEST CELL', nm:'Test Cell 5', col:'#d92020', st:'ACTIVE',
        temp:25.2, hum:60, pres:1012.8,
        rpm:1620, torque:228.6, power_out:38.7,
        temp_oli:75.8, temp_coolant:91.5,
        pres_oli:4.7, pres_bbm:7.5,
        flow_bbm:16.2, temp_exhaust:428,
        flow_coolant:98.8, pres_coolant:3.2,
        konsumsi_listrik:38.7,
        load:88.4, cycle:'4/6',
        test_id:'TC5-2024-092', test_dur:'04:15',
        phase:'HOLDING', phase_step:3
      },
      {
        id:'pit1', tp:'TEST PIT', nm:'Test Pit 1', col:'#d92020', st:'OPERATIONAL',
        temp:22.4, hum:55, pres:1014.0,
        // Pressure test
        test_pressure:285.4, target_pressure:300.0,
        pressure_rate:12.5, hold_time:30, hold_elapsed:18,
        // Maximator
        max_model:'M-150', max_inlet:8.2, max_outlet:285.4, max_ratio:'1:50',
        max_st:'RUNNING',
        // Fluida
        depth:3.5, fluid:'PARTIAL', fluid_type:'OLI HIDROLIK',
        flow_in:45.2, flow_out:44.8,
        fluid_temp:28.4,
        test_id:'TP1-2024-042', test_dur:'00:18',
        phase:'HOLDING', phase_step:3
      },
      {
        id:'pit2', tp:'TEST PIT', nm:'Test Pit 2', col:'#d92020', st:'OPERATIONAL',
        temp:22.9, hum:54, pres:1014.1,
        test_pressure:0, target_pressure:250.0,
        pressure_rate:0, hold_time:30, hold_elapsed:0,
        max_model:'M-200', max_inlet:8.5, max_outlet:0, max_ratio:'1:60',
        max_st:'STANDBY',
        depth:3.5, fluid:'EMPTY', fluid_type:'—',
        flow_in:0, flow_out:0, fluid_temp:24.2,
        test_id:'TP2-2024-043', test_dur:'00:00',
        phase:'IDLE', phase_step:0
      }
    ]
  },
  cr3:{
    tp:'CONTROL ROOM', nm:'Control Room 3', zn:'PHASE 1 · UPPER ZONE', col:'#1564c0',
    temp:22.1, hum:54, pwr:74.8, volt:220.4, freq:50.0, pf:0.93, st:'OPERATIONAL',
    locks:[
      {name:'Main Door',   st:'locked',   time:'09:00'},
      {name:'Server Rack', st:'locked',   time:'09:00'},
      {name:'Fire Exit',   st:'locked',   time:'09:00'},
      {name:'Cabinet C',   st:'unlocked', time:'11:15'}
    ],
    events:[
      {t:'14:15',c:'ok',m:'All sensors nominal'},
      {t:'13:50',c:'in',m:'Backup completed'},
      {t:'13:30',c:'ok',m:'Humidity 54% normal'},
      {t:'12:45',c:'in',m:'Temp 22.1°C logged'}
    ],
    rooms:[
      {
        id:'pit3', tp:'TEST PIT', nm:'Test Pit 3', col:'#059652', st:'OPERATIONAL',
        temp:21.8, hum:52, pres:1014.5,
        test_pressure:142.8, target_pressure:150.0,
        pressure_rate:8.4, hold_time:45, hold_elapsed:12,
        max_model:'M-100', max_inlet:7.8, max_outlet:142.8, max_ratio:'1:40',
        max_st:'RUNNING',
        depth:3.2, fluid:'PARTIAL', fluid_type:'AIR BERSIH',
        flow_in:38.4, flow_out:37.9, fluid_temp:26.2,
        test_id:'TP3-2024-038', test_dur:'00:12',
        phase:'HOLDING', phase_step:3
      },
      {
        id:'pit4', tp:'TEST PIT', nm:'Test Pit 4', col:'#c27a00', st:'STANDBY',
        temp:23.5, hum:56, pres:1013.8,
        test_pressure:0, target_pressure:180.0,
        pressure_rate:0, hold_time:60, hold_elapsed:0,
        max_model:'M-150', max_inlet:0, max_outlet:0, max_ratio:'1:50',
        max_st:'OFF',
        depth:2.8, fluid:'PARTIAL', fluid_type:'AIR BERSIH',
        flow_in:0, flow_out:0, fluid_temp:25.1,
        test_id:'TP4-2024-039', test_dur:'00:00',
        phase:'STANDBY', phase_step:1
      },
      {
        id:'pit5', tp:'TEST PIT', nm:'Test Pit 5', col:'#059652', st:'OPERATIONAL',
        temp:21.2, hum:50, pres:1014.8,
        test_pressure:95.6, target_pressure:100.0,
        pressure_rate:5.2, hold_time:30, hold_elapsed:8,
        max_model:'M-80', max_inlet:7.5, max_outlet:95.6, max_ratio:'1:30',
        max_st:'RUNNING',
        depth:4.2, fluid:'PARTIAL', fluid_type:'OLI HIDROLIK',
        flow_in:28.4, flow_out:28.1, fluid_temp:27.4,
        test_id:'TP5-2024-040', test_dur:'00:08',
        phase:'PRESSURIZING', phase_step:2
      },
      {
        id:'pit6', tp:'TEST PIT', nm:'Test Pit 6', col:'#059652', st:'OPERATIONAL',
        temp:21.4, hum:51, pres:1014.7,
        test_pressure:48.2, target_pressure:50.0,
        pressure_rate:3.8, hold_time:20, hold_elapsed:5,
        max_model:'M-60', max_inlet:7.2, max_outlet:48.2, max_ratio:'1:20',
        max_st:'RUNNING',
        depth:4.2, fluid:'PARTIAL', fluid_type:'AIR BERSIH',
        flow_in:18.8, flow_out:18.5, fluid_temp:25.8,
        test_id:'TP6-2024-041', test_dur:'00:05',
        phase:'PRESSURIZING', phase_step:2
      }
    ]
  }
};

/* ══ DATA UTILITAS (global) ══ */
const UTIL = {
  pam:{
    pump1:{ nm:'Pompa 1 (Utama)',  st:'RUNNING',  flow:125.4, pres:4.8, amp:18.2, temp_motor:62.4, run_hours:2840 },
    pump2:{ nm:'Pompa 2 (Cadangan)',st:'STANDBY', flow:0,     pres:0,   amp:0,    temp_motor:28.1, run_hours:1420 },
    tank_level:78,
    total_flow_hari:4820,
    last_service:'2025-01-15'
  },
  listrik:{
    total_kw:284.2, pf:0.92, freq:50.0,
    volt_r:220.4, volt_s:219.8, volt_t:221.2,
    amp_r:142.8, amp_s:140.2, amp_t:143.6,
    kwh_hari:1842.4,
    st:'NORMAL'
  },
  hvac:{
    unit1:{ nm:'AC CR1',  st:'RUNNING', set:22, actual:23.1, amp:8.4 },
    unit2:{ nm:'AC CR2',  st:'RUNNING', set:22, actual:23.8, amp:8.9 },
    unit3:{ nm:'AC CR3',  st:'RUNNING', set:22, actual:22.8, amp:8.1 },
    filter_st:'OK', next_service:'2025-03-01'
  }
};

/* ══ THREE.JS SETUP ══ */
const cv = document.getElementById('cv');
const renderer = new THREE.WebGLRenderer({canvas:cv, antialias:true, powerPreference:'high-performance'});
renderer.setPixelRatio(Math.min(devicePixelRatio, 2));
renderer.setSize(innerWidth, innerHeight);
renderer.shadowMap.enabled = true;
renderer.shadowMap.type = THREE.PCFSoftShadowMap;
renderer.toneMapping = THREE.ReinhardToneMapping;
renderer.toneMappingExposure = 1.1;

const scene = new THREE.Scene();
scene.background = new THREE.Color(0xdce8f5);
scene.fog = new THREE.FogExp2(0xdce8f5, .0053);

const Z = 22;
function getA(){ return innerWidth/innerHeight; }
const cam = new THREE.OrthographicCamera(-Z*getA(), Z*getA(), Z, -Z, .1, 500);
cam.position.set(42, 34, 42);
cam.lookAt(2, 1, 2);

scene.add(new THREE.AmbientLight(0xd0e0f0, 2.2));
const sun = new THREE.DirectionalLight(0xfff8e8, 2.9);
sun.position.set(28, 44, 22);
sun.castShadow = true;
sun.shadow.mapSize.set(4096, 4096);
const ss = sun.shadow.camera;
ss.left = ss.bottom = -44; ss.right = ss.top = 44;
ss.near = 1; ss.far = 160;
sun.shadow.bias = -.0008;
scene.add(sun);
const fillLight = new THREE.DirectionalLight(0x182840, .75);
fillLight.position.set(-22, 8, -22);
scene.add(fillLight);
scene.add(new THREE.HemisphereLight(0xc8dff5, 0x8aaccc, .80));

const ml = (h, e=0, ei=0) => new THREE.MeshLambertMaterial({color:h, emissive:e, emissiveIntensity:ei});
const mld = (h, e=0, ei=0) => new THREE.MeshLambertMaterial({color:h, emissive:e, emissiveIntensity:ei, side:THREE.DoubleSide});
const mt = (h, op, e=0, ei=0) => new THREE.MeshLambertMaterial({color:h, transparent:true, opacity:op, emissive:e, emissiveIntensity:ei});

const GW = () => [mld(0xdde8dd),mld(0xd0ddd0),mt(0x000000,0),mld(0x18202a),mld(0xdae6da),mld(0xd0ddd0)];
const RW = () => [mld(0xf5e8e8),mld(0xd0ddd0),mt(0x000000,0),mld(0x18202a),mld(0xdae6da),mld(0xd0ddd0)];
const CRW_BLUE = () => [
  ml(0x0a1828,0x041020,.35),ml(0x061018,0x020814,.25),
  ml(0x0e2038,0x062030,.55),ml(0x040810),
  ml(0x0c1e34,0x051828,.4),ml(0x061018)
];
const GLASS  = mt(0x3870c0,.55,0x183058,.55);
const GLASS2 = mt(0x2858a8,.42,0x0f1e3e,.6);
const FRAME  = ml(0x263040);
const MTL    = ml(0x1e2c3c);
const CONC   = ml(0x141e2c);
const CONC2  = ml(0x0e1620);
const LED    = ml(0x0e2e18,0x0ea040,1.4);

const BODIES = [];
function addm(geo,mat,x,y,z,rx=0,ry=0,rz=0){
  const me=new THREE.Mesh(geo,mat);
  me.position.set(x,y,z); me.rotation.set(rx,ry,rz);
  me.castShadow=true; me.receiveShadow=true;
  scene.add(me); return me;
}
const BOX=(w,h,d)=>new THREE.BoxGeometry(w,h,d);

function addEdge(w,h,d,x,y,z,col,op=1){
  const eg=new THREE.EdgesGeometry(BOX(w,h,d));
  const em=new THREE.LineSegments(eg,new THREE.LineBasicMaterial({color:col,transparent:op<1,opacity:op}));
  em.position.set(x,y,z); scene.add(em);
}
function addInnerColumns(x,z,w,d,h){
  const cw=0.04,ch=h,cy=h/2+.22,col=ml(0x1e2a38),ox=w/2-cw/2,oz=d/2-cw/2;
  addm(BOX(cw,ch,cw),col,x-ox,cy,z-oz); addm(BOX(cw,ch,cw),col,x+ox,cy,z-oz);
  addm(BOX(cw,ch,cw),col,x-ox,cy,z+oz); addm(BOX(cw,ch,cw),col,x+ox,cy,z+oz);
}
function addDoor(bx,bz,w,d,h,face,offAlong=0){
  const DW=0.85,DH=1.40,cy=h*.15+DH/2+.22;
  let px=bx,pz=bz,ry=0;
  if(face==='front'){pz=bz+d/2+.04;px=bx+offAlong;ry=0;}
  else if(face==='left'){px=bx-w/2-.04;pz=bz+offAlong;ry=Math.PI/2;}
  else if(face==='right'){px=bx+w/2+.04;pz=bz+offAlong;ry=-Math.PI/2;}
  const df=new THREE.Mesh(BOX(DW+.1,DH+.1,.08),FRAME);
  df.position.set(px,cy,pz); df.rotation.y=ry; df.castShadow=true; scene.add(df);
  const dg=new THREE.Mesh(BOX(DW,DH,.055),GLASS2);
  dg.position.set(px,cy,pz); dg.rotation.y=ry; scene.add(dg);
  const step=new THREE.Mesh(BOX(1.0,.06,.15),CONC);
  const so=face==='front'?[offAlong,0,d/2+.18]:face==='left'?[-w/2-.18,0,offAlong]:[w/2+.18,0,offAlong];
  step.position.set(bx+so[0],h*.18+.06,bz+so[2]); step.rotation.y=ry; step.castShadow=true; scene.add(step);
}
function addWins(bx,bz,w,d,h,face,nw=2){
  const WW=.68,WH=.54,wy=h*.54,sp=(face==='front'||face==='back'?w:d)/(nw+1);
  let ry=0;
  if(face==='left')ry=Math.PI/2;
  if(face==='right')ry=-Math.PI/2;
  for(let i=1;i<=nw;i++){
    const off=(i-(nw+1)/2)*sp;
    let wx=bx,wz=bz,fOff=0;
    if(face==='front'){fOff=d/2+.04;wx=bx+off;}
    if(face==='left'){fOff=-(w/2+.04);wz=bz+off;}
    if(face==='right'){fOff=w/2+.04;wz=bz+off;}
    const wpx=wx+(face==='left'||face==='right'?fOff:0);
    const wpz=wz+(face==='front'?fOff:0);
    const wf=new THREE.Mesh(BOX(WW+.1,WH+.08,.07),FRAME);
    wf.position.set(wpx,wy,wpz); wf.rotation.y=ry; wf.castShadow=true; scene.add(wf);
    const wg=new THREE.Mesh(BOX(WW,WH,.05),GLASS);
    wg.position.set(wpx,wy,wpz); wg.rotation.y=ry; scene.add(wg);
    const ws=new THREE.Mesh(BOX(WW+.15,.055,.12),MTL);
    ws.position.set(wpx,wy-WH/2-.03,wpz); ws.rotation.y=ry; scene.add(ws);
    const wl=new THREE.Mesh(BOX(WW*.8,.03,.02),LED);
    wl.position.set(wpx,wy+WH*.28,wpz); wl.rotation.y=ry; scene.add(wl);
  }
}
function building(id,x,z,w,d,h,mats,acol,doorCfg=null,winCfg=null,opts={}){
  const cy=h/2+.22;
  addm(BOX(w+.28,.22,d+.28),CONC2,x,.11,z);
  const body=new THREE.Mesh(BOX(w,h,d),mats);
  body.position.set(x,cy,z); body.castShadow=true; body.receiveShadow=true;
  if(id){body.userData.id=id;body.name=id;BODIES.push(body);}
  scene.add(body);
  addEdge(w,h,d,x,cy,z,0x4a5568,.85);
  if(doorCfg){const cfg=Array.isArray(doorCfg)?doorCfg:[doorCfg];cfg.forEach(dc=>addDoor(x,z,w,d,h,dc.face,dc.off||0));}
  if(winCfg) addWins(x,z,w,d,h,winCfg.face,winCfg.count||2);
  return body;
}

/* ══ GROUND & SCENE ══ */
addm(new THREE.PlaneGeometry(150,150),ml(0xc8d8ea),0,.001,0,-Math.PI/2);
const slb=new THREE.Mesh(new THREE.PlaneGeometry(40,34),ml(0x18202a));
slb.rotation.x=-Math.PI/2; slb.position.set(0,.01,2); slb.receiveShadow=true; scene.add(slb);
scene.add(new THREE.GridHelper(0,0));
[[40,.28,.32,0,.14,-15.2],[40,.28,.32,0,.14,19.2],[.32,.28,34,-20,.14,2],[.32,.28,34,20,.14,2]]
  .forEach(([w,h,d,x,y,z])=>{addm(BOX(w,h,d),ml(0x8aaac8),x,y,z);addEdge(w,h,d,x,y,z,0x6090b8,.65);});

const roadMat=ml(0xd4a000,0xffd000,.35);
function addRoad(x1,z1,x2,z2,width=1.0){
  const dx=x2-x1,dz=z2-z1,len=Math.sqrt(dx*dx+dz*dz),angle=Math.atan2(dx,dz);
  addm(BOX(width,.05,len),roadMat,(x1+x2)/2,.04,(z1+z2)/2,0,angle,0);
}
addRoad(-10,-15,-10,3.5,1); addRoad(6.7,-4,6.7,3.5,1);
addRoad(-20,2,-10,2,1); addRoad(-10,3,7,3,1); addRoad(7,-3.5,19.5,-3.5,1);

/* ══ BUILDINGS ══ */
building('pit3',-14,-9,6.3,5.5,3.0,GW(),0x4a8460,{face:'right',off:0},null);
addInnerColumns(-14,-9,6.3,5.5,3.0);
building('pit4',-14,-3.4,6.3,5.4,3.0,RW(),0xa83848,{face:'right',off:0},null);
addInnerColumns(-14,-3.4,6.3,5.4,3.0);
building('cr3',-5.5,-11,4.0,2.0,2.0,CRW_BLUE(),0x1a8fff,{face:'front',off:0.7},{face:'front',count:2});
building('cell5',1.8,-10,6.5,6,3.0,GW(),0xa83848,{face:'right',off:2},null);
addm(BOX(6.5,.06,6.0),ml(0xc8960c),1.8,3.22,-10);
building('cell4',1.8,-3.8,6.5,6,3.0,RW(),0xa83848,{face:'front',off:2.2},null);
addm(BOX(6.5,.06,6.0),ml(0xc8960c),1.8,3.22,-3.8);
building('pit1',10.8,-9,4.5,7,3.0,RW(),0xa83848,{face:'front',off:-1.3},null);
addInnerColumns(10.8,-9,4.5,7.0,3.0);
building('pit2',15.5,-9,4.5,7,3.0,RW(),0xa83848,{face:'front',off:1.3},null);
addInnerColumns(15.5,-9,4.5,7.0,3.0);
building('cr1',10.5,1.5,4.0,2.0,2.0,CRW_BLUE(),0x1a8fff,{face:'front',off:0.7},{face:'front',count:2});
building('cr2',15.5,1.5,4.0,2.0,2.0,CRW_BLUE(),0x1a8fff,{face:'front',off:0.7},{face:'front',count:2});
building('pit5',-12.3,11,5.8,6.5,3.0,GW(),0x4a8460,null,null);
addInnerColumns(-12.3,11,5.8,6.5,3.0);
building('pit6',-6.2,11,5.8,6.5,3.0,GW(),0x4a8460,null,null);
addInnerColumns(-6.2,11,5.8,6.5,3.0);
building('cell3',1.8,13,6.7,6.1,3.0,GW(),0x4a8460,null,null);
addm(BOX(6.7,.06,6.1),ml(0xc8960c),1.8,3.22,13);
building('cell2',8.6,13,6.7,6.1,3.0,GW(),0x4a8460,null,null);
addm(BOX(6.7,.06,6.1),ml(0xc8960c),8.6,3.22,13);
building('cell1',15.5,13,6.7,6.1,3.0,GW(),0x4a8460,null,null);
addm(BOX(6.7,.06,6.1),ml(0xc8960c),15.5,3.22,13);

/* ══ LABELS ══ */
const LBLS = [
  {id:'cr3',   anchor:new THREE.Vector3(-5.5,2.5,-11),  t:'CONTROL ROOM 3', ox:80,   oy:-120, by:-120},
  {id:'cr1',   anchor:new THREE.Vector3(10.5,2.5,1.5),  t:'CONTROL ROOM 1', ox:140,  oy:130,  by:0},
  {id:'cr2',   anchor:new THREE.Vector3(15.5,2.5,1.5),  t:'CONTROL ROOM 2', ox:60,   oy:140,  by:0},
  {id:'pit3',  anchor:new THREE.Vector3(-14,3.2,-9),     t:'TEST PIT 3',     ox:-80,  oy:-70,  by:-70},
  {id:'pit4',  anchor:new THREE.Vector3(-14,3.2,-3.4),   t:'TEST PIT 4',     ox:-120, oy:-60,  by:-60},
  {id:'pit5',  anchor:new THREE.Vector3(-12.3,3.2,11),   t:'TEST PIT 5',     ox:-110, oy:-70,  by:-70},
  {id:'pit6',  anchor:new THREE.Vector3(-6.2,3.2,11),    t:'TEST PIT 6',     ox:-215, oy:-25,  by:-25},
  {id:'cell5', anchor:new THREE.Vector3(1.8,3.2,-10),    t:'TEST CELL 5',    ox:80,   oy:-120, by:-120},
  {id:'cell4', anchor:new THREE.Vector3(1.8,3.2,-3.8),   t:'TEST CELL 4',    ox:160,  oy:-60,  by:-60},
  {id:'pit1',  anchor:new THREE.Vector3(10.8,3.2,-9),    t:'TEST PIT 1',     ox:90,   oy:-50,  by:-50},
  {id:'pit2',  anchor:new THREE.Vector3(15.5,3.2,-9),    t:'TEST PIT 2',     ox:80,   oy:-40,  by:-40},
  {id:'cell3', anchor:new THREE.Vector3(1.8,3.2,13),     t:'TEST CELL 3',    ox:-70,  oy:110,  by:0},
  {id:'cell2', anchor:new THREE.Vector3(8.6,3.2,13),     t:'TEST CELL 2',    ox:-70,  oy:125,  by:0},
  {id:'cell1', anchor:new THREE.Vector3(15.5,3.2,13),    t:'TEST CELL 1',    ox:-70,  oy:130,  by:0},
];
const lyrEl=document.getElementById('lyr');
const svgEl=document.getElementById('lyr-svg');
LBLS.forEach(lb=>{
  const div=document.createElement('div');
  div.className='lbl lbl-ext';
  div.innerHTML=`<div class="lbl-ext-name">${lb.t}</div>`;
  div.style.opacity='0';
  div.addEventListener('click',()=>{
    if(DATA[lb.id]){
      // CR → buka panel CR
      openPanel(lb.id);
    } else {
      // Room → buka panel room langsung
      const crId=findCR(lb.id);
      if(crId) openRoomPanel(lb.id,crId);
    }
  });
  lyrEl.appendChild(div);
  lb.el=div;
});
// buildSidebarRooms() dipanggil setelah ROOM_TO_CR dan findCR didefinisikan (di bawah)

/* map room id → parent CR id */
const ROOM_TO_CR={};
function findCR(id){
  if(DATA[id]) return id;
  return ROOM_TO_CR[id]||null;
}
function findRoom(roomId){
  for(const d of Object.values(DATA)){
    const r=d.rooms.find(x=>x.id===roomId);
    if(r) return r;
  }
  return null;
}

/* ══ RAYCASTING ══ */
const RC=new THREE.Raycaster(),MV=new THREE.Vector2();
const tipEl=document.getElementById('tip');
let hov=null,mdx=0,mdy=0,curId=null,curRoomId=null,lv=null,panelMode='cr';

cv.addEventListener('mousemove',e=>{
  MV.x=(e.clientX/innerWidth)*2-1;
  MV.y=-((e.clientY/innerHeight)*2-1);
  RC.setFromCamera(MV,cam);
  const hits=RC.intersectObjects(BODIES,false);
  if(hits.length){
    const id=hits[0].object.userData.id;
    const crId=findCR(id);
    if(crId){
      hov=id; cv.style.cursor='pointer';
      const isCR=!!DATA[id];
      const d=DATA[crId];
      tipEl.style.borderTopColor=isCR?'#1564c0':d.col;
      tipEl.style.color=isCR?'#1564c0':d.col;
      const roomD=isCR?null:findRoom(id);
      const label=roomD?`${roomD.nm}`:d.nm;
      tipEl.textContent=`${label} · Klik untuk detail`;
      tipEl.style.left=(e.clientX+14)+'px'; tipEl.style.top=(e.clientY-14)+'px';
      tipEl.style.opacity='1';
    }
  } else {hov=null;cv.style.cursor='';tipEl.style.opacity='0';}
});
cv.addEventListener('mousedown',e=>{mdx=e.clientX;mdy=e.clientY;});
cv.addEventListener('click',e=>{
  if(Math.abs(e.clientX-mdx)>6||Math.abs(e.clientY-mdy)>6)return;
  MV.x=(e.clientX/innerWidth)*2-1;
  MV.y=-((e.clientY/innerHeight)*2-1);
  RC.setFromCamera(MV,cam);
  const hits=RC.intersectObjects(BODIES,false);
  if(hits.length){
    const id=hits[0].object.userData.id;
    if(DATA[id]){
      // Klik langsung CR building → buka panel CR
      openPanel(id);
    } else {
      // Klik room/cell/pit building → buka panel room langsung
      const crId=findCR(id);
      if(crId) openRoomPanel(id,crId);
    }
  }
});

/* ══ SIDEBAR ROOMS BUILD ══ */
function buildSidebarRooms(){
  Object.entries(DATA).forEach(([crId,d])=>{
    const cont=document.getElementById('rooms-'+crId);
    if(!cont)return;
    cont.innerHTML=d.rooms.map(r=>{
      ROOM_TO_CR[r.id]=crId;
      const isCell=r.tp==='TEST CELL';
      const stCl=r.st==='ACTIVE'||r.st==='OPERATIONAL'?'ok':r.st==='STANDBY'?'sb':'wa';
      const ico=isCell?'ti-engine':'ti-arrows-down';
      return `<div class="room-item" id="ri-${r.id}" onclick="openRoomPanel('${r.id}','${crId}')">
        <div class="room-dot" style="background:${r.col}"></div>
        <i class="ti ${ico}" style="font-size:11px;color:var(--muted);flex-shrink:0"></i>
        <div class="room-nm">${r.nm}</div>
        <div class="room-st ${stCl}" id="rst-${r.id}">${r.st}</div>
      </div>`;
    }).join('');
  });
}

/* ══ TOGGLE EXPAND ══ */
function toggleRooms(crId){
  const cont=document.getElementById('rooms-'+crId);
  const btn=document.getElementById('exp-'+crId);
  const isOpen=cont.classList.contains('open');
  // close all
  ['cr1','cr2','cr3'].forEach(k=>{
    document.getElementById('rooms-'+k).classList.remove('open');
    document.getElementById('exp-'+k).classList.remove('open');
  });
  if(!isOpen){
    cont.classList.add('open');
    btn.classList.add('open');
  }
}

/* ══ PANEL — ROOM LANGSUNG ══ */
function openRoomPanel(roomId, crId){
  console.log("ROOM DIKLIK");
  const r=findRoom(roomId);
  const crD=DATA[crId];
  if(!r||!crD)return;

  // ✅ TARUH DI SINI
  showCamera();

  panelMode='room'; 
  curRoomId=roomId; 
  curId=crId;

  const rp=document.getElementById('rpanel');
  const col=r.col||'#059652';
  document.getElementById('rp-accent').style.background=col;
  rp.style.setProperty('--ra',col);


  // Breadcrumb
  const bc=document.getElementById('rp-breadcrumb');
  bc.style.display='flex';
  bc.innerHTML=`<span class="bc-link" onclick="openPanel('${crId}')">${crD.nm}</span>
    <span class="bc-sep">›</span>
    <span>${r.nm}</span>`;

  document.getElementById('rp-type').textContent=r.tp;
  document.getElementById('rp-name').textContent=r.nm;
  document.getElementById('rp-name').style.color=col;
  document.getElementById('rp-zone').textContent=`Dimonitor: ${crD.nm}`;

  // Tabs: hanya SENSOR (env) + MESIN atau PRESSURE + LOG
  ['tab-s','tab-l','tab-r','tab-e','tab-u','tab-m','tab-p'].forEach(t=>
    document.getElementById(t).style.display='none');
  document.getElementById('tab-s').style.display='';
  document.getElementById('tab-e').style.display='';
  if(r.tp==='TEST CELL'){
    document.getElementById('tab-m').style.display='';
  } else {
    document.getElementById('tab-p').style.display='';
  }

  renderRoomEnv(r);
  if(r.tp==='TEST CELL') renderRoomCell(r);
  else renderRoomPit(r);
  renderEvents(crId);
  swTab('s');
  rp.classList.add('open');

  // Highlight sidebar
  ['cr1','cr2','cr3'].forEach(k=>document.getElementById('nav-'+k).classList.remove('active'));
  document.getElementById('nav-'+crId).classList.add('active');
  document.querySelectorAll('.room-item').forEach(el=>el.classList.remove('active'));
  const ri=document.getElementById('ri-'+roomId);
  if(ri) ri.classList.add('active');

  // Auto-expand CR di sidebar
  const cont=document.getElementById('rooms-'+crId);
  const btn=document.getElementById('exp-'+crId);
  cont.classList.add('open');
  btn.classList.add('open');

  if(lv)clearInterval(lv);
  lv=setInterval(()=>{
    if(panelMode!=='room'||!curRoomId)return;
    const crIdCur=findCR(curRoomId);
    if(crIdCur) tickRoom(crIdCur);
    const rr=findRoom(curRoomId);
    if(!rr)return;
    renderRoomEnv(rr);
    if(rr.tp==='TEST CELL') renderRoomCell(rr);
    else renderRoomPit(rr);
  },3000);
}

/* ENV SENSOR RUANGAN */
function renderRoomEnv(r){
  const tW=r.temp>25, hH=r.hum>60;
  document.getElementById('pane-s').innerHTML=`
  <button class="rp-back" onclick="openPanel('${findCR(r.id)}')">
    <i class="ti ti-arrow-left"></i>KEMBALI KE CONTROL ROOM
  </button>
  <div class="rp-sec">SENSOR RUANGAN</div>
  <div class="mc-grid">
    <div class="mc T">
      <div class="mc-lbl">TEMPERATURE</div>
      <div class="mc-val">${r.temp} °C</div>
      <div class="mc-st ${tW?'wa':'ok'}">${tW?'TINGGI':'NORMAL'}</div>
    </div>
    <div class="mc H">
      <div class="mc-lbl">HUMIDITY</div>
      <div class="mc-val">${r.hum} % RH</div>
      <div class="mc-st ${hH?'wa':'ok'}">${hH?'TINGGI':'NORMAL'}</div>
    </div>
    <div class="mc P">
      <div class="mc-lbl">PRESSURE</div>
      <div class="mc-val sm">${r.pres||1013} hPa</div>
      <div class="mc-st ok">NORMAL</div>
    </div>
  </div>
  <div style="margin-top:8px;background:var(--s1);border:1px solid var(--b1);border-radius:5px;padding:10px 12px">
    <div style="font-family:var(--M);font-size:9px;color:var(--dim);letter-spacing:2px;margin-bottom:6px;font-weight:600">INFO TEST</div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:5px">
      <div style="font-family:var(--M);font-size:9px;color:var(--muted)">TEST ID</div>
      <div style="font-family:var(--M);font-size:9px;color:var(--txt);font-weight:600">${r.test_id||'—'}</div>
      <div style="font-family:var(--M);font-size:9px;color:var(--muted)">FASE</div>
      <div style="font-family:var(--M);font-size:9px;color:var(--amber);font-weight:600">${r.phase||'—'}</div>
      <div style="font-family:var(--M);font-size:9px;color:var(--muted)">DURASI</div>
      <div style="font-family:var(--M);font-size:9px;color:var(--in);font-weight:600">${r.test_dur||'—'}</div>
      <div style="font-family:var(--M);font-size:9px;color:var(--muted)">STATUS</div>
      <div style="font-family:var(--M);font-size:9px;color:var(--ok);font-weight:600">${r.st||'—'}</div>
    </div>
  </div>
  <div style="margin-top:6px;font-family:var(--M);font-size:9px;color:var(--dim);text-align:right">
    Update: <span style="color:var(--ok)">${new Date().toLocaleTimeString('en-GB')}</span>
  </div>`;
}

/* MESIN — TEST CELL */
function renderRoomCell(r){
  const tOil=r.temp_oli>80?'wa':r.temp_oli>70?'amb':'ok';
  const tCool=r.temp_coolant>90?'cr':r.temp_coolant>82?'wa':'ok';
  const phaseColors=['var(--dim)','var(--in)','var(--ok)','var(--amber)','var(--ok)'];
  const phaseIdx=r.phase_step||0;
  document.getElementById('pane-m').innerHTML=`
  <div class="rp-sec">PARAMETER MESIN</div>
  <div class="rd-env">
    <div class="rd-env-hdr">
      <i class="ti ti-engine" style="color:var(--ok);font-size:14px"></i>
      <div class="rd-env-title">PERFORMA</div>
      <div style="font-family:var(--M);font-size:8px;color:var(--ok)">● RUNNING</div>
    </div>
    <div class="rd-env-body">
      <div class="rd-grid">
        <div class="rd-cell">
          <div class="rd-cell-lbl">RPM</div>
          <div class="rd-cell-val in">${r.rpm}</div>
        </div>
        <div class="rd-cell">
          <div class="rd-cell-lbl">TORQUE</div>
          <div class="rd-cell-val">${r.torque} <span style="font-size:9px;color:var(--muted)">Nm</span></div>
        </div>
        <div class="rd-cell">
          <div class="rd-cell-lbl">OUTPUT</div>
          <div class="rd-cell-val ok">${r.power_out} <span style="font-size:9px;color:var(--muted)">kW</span></div>
        </div>
      </div>
      <div style="margin-top:5px;background:var(--s2);border-radius:3px;padding:7px 8px">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:5px">
          <div style="font-family:var(--M);font-size:8px;color:var(--muted)">LOAD</div>
          <div style="font-family:var(--M);font-size:10px;font-weight:700;color:${r.load>85?'var(--wa)':'var(--ok)'}">${r.load}%</div>
        </div>
        <div style="height:4px;background:var(--b1);border-radius:2px;overflow:hidden">
          <div style="height:100%;width:${Math.min(r.load,100)}%;background:${r.load>85?'var(--wa)':'var(--ok)'};border-radius:2px;transition:width 1s"></div>
        </div>
      </div>
    </div>
  </div>
  <div class="rd-env">
    <div class="rd-env-hdr">
      <i class="ti ti-temperature" style="color:var(--amber);font-size:14px"></i>
      <div class="rd-env-title">TERMAL</div>
    </div>
    <div class="rd-env-body">
      <div class="rd-grid">
        <div class="rd-cell">
          <div class="rd-cell-lbl">T. OLI</div>
          <div class="rd-cell-val ${tOil}">${r.temp_oli}°C</div>
        </div>
        <div class="rd-cell">
          <div class="rd-cell-lbl">T. COOLANT</div>
          <div class="rd-cell-val ${tCool}">${r.temp_coolant}°C</div>
        </div>
        <div class="rd-cell">
          <div class="rd-cell-lbl">T. EXHAUST</div>
          <div class="rd-cell-val amb">${r.temp_exhaust}°C</div>
        </div>
      </div>
    </div>
  </div>
  <div class="rd-env">
    <div class="rd-env-hdr">
      <i class="ti ti-droplet" style="color:var(--in);font-size:14px"></i>
      <div class="rd-env-title">BAHAN BAKAR & COOLANT</div>
    </div>
    <div class="rd-env-body">
      <div class="rd-grid2">
        <div class="rd-cell">
          <div class="rd-cell-lbl">FLOW BBM</div>
          <div class="rd-cell-val">${r.flow_bbm} <span style="font-size:9px;color:var(--muted)">L/h</span></div>
        </div>
        <div class="rd-cell">
          <div class="rd-cell-lbl">PRES. BBM</div>
          <div class="rd-cell-val">${r.pres_bbm} <span style="font-size:9px;color:var(--muted)">bar</span></div>
        </div>
        <div class="rd-cell">
          <div class="rd-cell-lbl">FLOW COOLANT</div>
          <div class="rd-cell-val">${r.flow_coolant} <span style="font-size:9px;color:var(--muted)">L/m</span></div>
        </div>
        <div class="rd-cell">
          <div class="rd-cell-lbl">KONSUMSI</div>
          <div class="rd-cell-val">${r.konsumsi_listrik} <span style="font-size:9px;color:var(--muted)">kW</span></div>
        </div>
      </div>
    </div>
  </div>
  <div style="margin-top:4px;background:var(--s1);border:1px solid var(--b1);border-radius:5px;padding:8px 12px">
    <div style="font-family:var(--M);font-size:8px;color:var(--muted);margin-bottom:5px;letter-spacing:1.5px">FASE TEST</div>
    <div style="display:flex;gap:3px;margin-bottom:4px">
      ${['IDLE','SETUP','RUNNING','HOLDING','COMPLETE'].map((ph,i)=>`
        <div style="flex:1;height:4px;border-radius:2px;background:${i<phaseIdx?'var(--ok)':i===phaseIdx?'var(--amber)':'var(--b1)'}"></div>
      `).join('')}
    </div>
    <div style="font-family:var(--M);font-size:9px;color:${phaseColors[phaseIdx]};font-weight:600;text-align:center">${r.phase}</div>
  </div>`;
}

/* PRESSURE — TEST PIT */
function renderRoomPit(r){
  const pct=r.target_pressure>0?Math.round(r.test_pressure/r.target_pressure*100):0;
  const pCol=pct>=100?'var(--ok)':pct>80?'var(--amber)':'var(--in)';
  const maxSt=r.max_st==='RUNNING'?'ok':r.max_st==='STANDBY'?'sb':'cr';
  document.getElementById('pane-p').innerHTML=`
  <div class="rp-sec">TEST PRESSURE</div>
  <div class="rd-env">
    <div class="rd-env-hdr">
      <i class="ti ti-gauge" style="color:${pCol};font-size:14px"></i>
      <div class="rd-env-title">PRESSURE GAUGE</div>
      <div style="font-family:var(--M);font-size:8px;color:${pCol}">${pct}%</div>
    </div>
    <div class="rd-env-body">
      <div style="text-align:center;margin-bottom:8px">
        <div style="font-family:var(--M);font-size:36px;font-weight:700;color:${pCol};line-height:1">${r.test_pressure}</div>
        <div style="font-family:var(--M);font-size:10px;color:var(--muted)">bar</div>
      </div>
      <div style="height:6px;background:var(--b1);border-radius:4px;overflow:hidden;margin-bottom:6px">
        <div style="height:100%;width:${Math.min(pct,100)}%;background:${pCol};border-radius:4px;transition:width 1.2s"></div>
      </div>
      <div style="display:flex;justify-content:space-between">
        <div style="font-family:var(--M);font-size:8px;color:var(--muted)">0 bar</div>
        <div style="font-family:var(--M);font-size:8px;color:var(--txt);font-weight:600">TARGET: ${r.target_pressure} bar</div>
      </div>
      <div style="margin-top:8px;display:grid;grid-template-columns:1fr 1fr;gap:5px">
        <div class="rd-cell">
          <div class="rd-cell-lbl">RATE</div>
          <div class="rd-cell-val">${r.pressure_rate} <span style="font-size:9px;color:var(--muted)">bar/min</span></div>
        </div>
        <div class="rd-cell">
          <div class="rd-cell-lbl">HOLD TIME</div>
          <div class="rd-cell-val">${r.hold_time} <span style="font-size:9px;color:var(--muted)">min</span></div>
        </div>
        <div class="rd-cell">
          <div class="rd-cell-lbl">FASE</div>
          <div class="rd-cell-val amb">${r.phase}</div>
        </div>
        <div class="rd-cell">
          <div class="rd-cell-lbl">DURASI</div>
          <div class="rd-cell-val in">${r.test_dur}</div>
        </div>
      </div>
    </div>
  </div>
  <div class="rd-env">
    <div class="rd-env-hdr">
      <i class="ti ti-device-analytics" style="color:var(--amber);font-size:14px"></i>
      <div class="rd-env-title">MAXIMATOR ${r.max_model}</div>
      <div class="rm-st ${maxSt}" style="font-size:8px">${r.max_st}</div>
    </div>
    <div class="rd-env-body">
      <div class="rd-grid">
        <div class="rd-cell">
          <div class="rd-cell-lbl">INLET</div>
          <div class="rd-cell-val">${r.max_inlet} <span style="font-size:9px;color:var(--muted)">bar</span></div>
        </div>
        <div class="rd-cell">
          <div class="rd-cell-lbl">OUTLET</div>
          <div class="rd-cell-val ${pct>90?'wa':'ok'}">${r.max_outlet} <span style="font-size:9px;color:var(--muted)">bar</span></div>
        </div>
        <div class="rd-cell">
          <div class="rd-cell-lbl">RATIO</div>
          <div class="rd-cell-val">${r.max_ratio}</div>
        </div>
      </div>
    </div>
  </div>
  <div class="rd-env">
    <div class="rd-env-hdr">
      <i class="ti ti-droplet" style="color:var(--in);font-size:14px"></i>
      <div class="rd-env-title">FLUIDA</div>
    </div>
    <div class="rd-env-body">
      <div class="rd-grid">
        <div class="rd-cell">
          <div class="rd-cell-lbl">FLOW IN</div>
          <div class="rd-cell-val">${r.flow_in} <span style="font-size:9px;color:var(--muted)">L/m</span></div>
        </div>
        <div class="rd-cell">
          <div class="rd-cell-lbl">JENIS</div>
          <div class="rd-cell-val in">${r.fluid_type||'—'}</div>
        </div>
        <div class="rd-cell">
          <div class="rd-cell-lbl">T. FLUIDA</div>
          <div class="rd-cell-val">${r.fluid_temp}°C</div>
        </div>
      </div>
    </div>
  </div>`;
}

/* ══ PANEL — CR ══ */
function openPanel(id){
  if(!DATA[id])return;
  panelMode='cr'; curId=id; curRoomId=null;
  const d=DATA[id];
  const rp=document.getElementById('rpanel');
  document.getElementById('rp-accent').style.background='#1564c0';
  rp.style.setProperty('--ra','#1564c0');

  // Reset breadcrumb
  const bc=document.getElementById('rp-breadcrumb');
  bc.style.display='none'; bc.innerHTML='';

  document.getElementById('rp-type').textContent=d.tp;
  document.getElementById('rp-name').textContent=d.nm;
  document.getElementById('rp-name').style.color='#1564c0';
  document.getElementById('rp-zone').textContent=d.zn;
  // Tampilkan tab normal CR, sembunyikan tab room
  ['tab-s','tab-l','tab-r','tab-e'].forEach(t=>document.getElementById(t).style.display='');
  ['tab-u','tab-m','tab-p'].forEach(t=>document.getElementById(t).style.display='none');
  updatePanelStatus(id);
  renderSensor(id); renderLock(id); renderRooms(id); renderEvents(id);
  swTab('s');
  rp.classList.add('open');
  ['cr1','cr2','cr3'].forEach(k=>document.getElementById('nav-'+k).classList.toggle('active',k===id));
  // Clear room item highlights
  document.querySelectorAll('.room-item').forEach(el=>el.classList.remove('active'));
  if(lv)clearInterval(lv);
  lv=setInterval(()=>{if(!curId||panelMode!=='cr')return;tickRoom(curId);renderSensor(curId);updatePanelStatus(curId);},3200);
}

function openUtilPanel(){
  panelMode='util'; curId=null;
  const rp=document.getElementById('rpanel');
  document.getElementById('rp-accent').style.background='#0369a1';
  rp.style.setProperty('--ra','#0369a1');
  document.getElementById('rp-type').textContent='SISTEM UTILITAS';
  document.getElementById('rp-name').textContent='Utilitas Fasilitas';
  document.getElementById('rp-name').style.color='#0369a1';
  document.getElementById('rp-zone').textContent='PAM · LISTRIK · HVAC';
  // Tampilkan hanya tab UTILITAS
  ['tab-s','tab-l','tab-r','tab-e'].forEach(t=>document.getElementById(t).style.display='none');
  document.getElementById('tab-u').style.display='';
  swTab('u');
  renderUtil();
  rp.classList.add('open');
  ['cr1','cr2','cr3'].forEach(k=>document.getElementById('nav-'+k).classList.remove('active'));
  if(lv)clearInterval(lv);
  lv=setInterval(()=>{if(panelMode!=='util')return;tickUtil();renderUtil();updateUtilSidebar();},3000);
}

function updatePanelStatus(id){}
function closePanel(){
  document.getElementById('rpanel').classList.remove('open');
  curId=null; panelMode='none';
  if(lv){clearInterval(lv);lv=null;}
  ['cr1','cr2','cr3'].forEach(k=>document.getElementById('nav-'+k).classList.remove('active'));
}

/* ══ RENDER SENSOR CR ══ */
function renderSensor(id){
  const d=DATA[id];
  const tH=d.temp>26,hH=d.hum>60,pW=d.pwr>90;
  document.getElementById('pane-s').innerHTML=`
  <div class="rp-sec">SENSOR CONTROL ROOM</div>
  <div class="mc-grid">
    <div class="mc T">
      <div class="mc-lbl">TEMPERATURE</div>
      <div class="mc-val">${d.temp} °C</div>
      <div class="mc-st ${tH?'wa':'ok'}">${tH?'TINGGI':'NORMAL'}</div>
    </div>
    <div class="mc H">
      <div class="mc-lbl">HUMIDITY</div>
      <div class="mc-val">${d.hum} % RH</div>
      <div class="mc-st ${hH?'wa':'ok'}">${hH?'TINGGI':'NORMAL'}</div>
    </div>
    <div class="mc W">
      <div class="mc-lbl">POWER LOAD</div>
      <div class="mc-val">${d.pwr} %</div>
      <div class="mc-st ${pW?'wa':'ok'}">${pW?'TINGGI':'NORMAL'}</div>
    </div>
    <div class="mc P">
      <div class="mc-lbl">VOLTAGE</div>
      <div class="mc-val">${d.volt} V</div>
      <div class="mc-st ok">STABIL</div>
    </div>
    <div class="mc EG">
      <div class="mc-lbl">FREQUENCY</div>
      <div class="mc-val">${d.freq.toFixed(1)} Hz</div>
      <div class="mc-st ok">NORMAL</div>
    </div>
    <div class="mc CY">
      <div class="mc-lbl">POWER FACTOR</div>
      <div class="mc-val">${d.pf.toFixed(2)}</div>
      <div class="mc-st ${d.pf>=0.85?'ok':'wa'}">${d.pf>=0.85?'BAIK':'RENDAH'}</div>
    </div>
  </div>
  <div style="margin-top:10px;font-family:var(--M);font-size:9px;color:var(--dim);text-align:right">
    Update: <span style="color:var(--ok)">${new Date().toLocaleTimeString('en-GB')}</span>
  </div>`;
}

/* ══ RENDER LOCK ══ */
function renderLock(id){
  const d=DATA[id];
  const locked=d.locks.filter(l=>l.st==='locked').length,total=d.locks.length;
  const pct=Math.round(locked/total*100),sc=locked<total?'var(--wa)':'var(--ok)';
  let h=`
  <div class="rp-sec">LOCK SYSTEM</div>
  <div style="background:var(--s1);border:1px solid var(--b1);border-radius:5px;
    padding:12px 14px;margin-bottom:10px;display:flex;align-items:center;justify-content:space-between">
    <div>
      <div style="font-family:var(--M);font-size:10px;color:var(--muted);letter-spacing:2px;margin-bottom:4px;font-weight:600">TERKUNCI</div>
      <div style="font-family:var(--M);font-size:24px;font-weight:700;color:${sc}">${locked}<span style="font-size:10px;color:var(--dim)"> / ${total}</span></div>
    </div>
    <div style="text-align:right">
      <div style="font-family:var(--M);font-size:10px;color:var(--dim);margin-bottom:6px;font-weight:600">KEAMANAN</div>
      <div style="width:80px;height:5px;background:var(--b1);border-radius:3px;overflow:hidden;margin-bottom:5px">
        <div style="height:100%;width:${pct}%;background:${sc};border-radius:3px"></div>
      </div>
      <div style="font-family:var(--M);font-size:10px;color:${sc};font-weight:600">${pct}%</div>
    </div>
  </div>`;
  d.locks.forEach(l=>{
    h+=`<div class="lk-item">
      <i class="ti ${l.st==='locked'?'ti-lock':'ti-lock-open'} lk-ico" style="color:${l.st==='locked'?'var(--ok)':'var(--cr)'}"></i>
      <div style="flex:1"><div class="lk-name">${l.name}</div><div class="lk-time">${l.time}</div></div>
      <div class="lk-badge ${l.st==='locked'?'lk':'ul'}">${l.st==='locked'?'LOCKED':'OPEN'}</div>
    </div>`;
  });
  document.getElementById('pane-l').innerHTML=h;
}

/* ══ RENDER ROOMS ══ */
function renderRooms(id){
  const d=DATA[id];
  let h=`<div class="rp-sec">RUANGAN DIMONITOR (${d.rooms.length})</div>`;
  d.rooms.forEach(r=>{
    const isCell=r.tp==='TEST CELL',isPit=r.tp==='TEST PIT';
    const stCl=r.st==='ACTIVE'||r.st==='OPERATIONAL'?'ok':r.st==='STANDBY'?'sb':'wa';

    if(isCell){
      const phaseColors=['var(--dim)','var(--in)','var(--ok)','var(--amber)','var(--ok)'];
      const phaseIdx=r.phase_step;
      h+=`<div class="rm-row" style="flex-direction:column;align-items:stretch;gap:6px">
        <div style="display:flex;align-items:center;gap:8px;cursor:pointer" onclick="openRoomPanel('${r.id}','${id}')">
          <div class="rm-dot" style="background:${r.col}"></div>
          <div class="rm-info">
            <div class="rm-name">${r.nm}</div>
            <div class="rm-type">${r.tp} · <span style="color:var(--muted)">${r.test_id}</span></div>
          </div>
          <div class="rm-st ${stCl}">${r.st}</div>
          <i class="ti ti-chevron-right" style="font-size:12px;color:var(--muted);flex-shrink:0"></i>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:4px;padding:0 2px">
          <div style="background:var(--s2);border-radius:3px;padding:5px 7px">
            <div style="font-family:var(--M);font-size:8px;color:var(--muted)">RPM</div>
            <div style="font-family:var(--M);font-size:11px;font-weight:600;color:var(--txt)">${r.rpm}</div>
          </div>
          <div style="background:var(--s2);border-radius:3px;padding:5px 7px">
            <div style="font-family:var(--M);font-size:8px;color:var(--muted)">TORQUE</div>
            <div style="font-family:var(--M);font-size:11px;font-weight:600;color:var(--txt)">${r.torque} Nm</div>
          </div>
          <div style="background:var(--s2);border-radius:3px;padding:5px 7px">
            <div style="font-family:var(--M);font-size:8px;color:var(--muted)">OUTPUT</div>
            <div style="font-family:var(--M);font-size:11px;font-weight:600;color:var(--txt)">${r.power_out} kW</div>
          </div>
          <div style="background:var(--s2);border-radius:3px;padding:5px 7px">
            <div style="font-family:var(--M);font-size:8px;color:var(--muted)">T.OLI</div>
            <div style="font-family:var(--M);font-size:11px;font-weight:600;color:${r.temp_oli>80?'var(--wa)':'var(--txt)'}">${r.temp_oli}°C</div>
          </div>
          <div style="background:var(--s2);border-radius:3px;padding:5px 7px">
            <div style="font-family:var(--M);font-size:8px;color:var(--muted)">T.COOLANT</div>
            <div style="font-family:var(--M);font-size:11px;font-weight:600;color:${r.temp_coolant>90?'var(--wa)':'var(--txt)'}">${r.temp_coolant}°C</div>
          </div>
          <div style="background:var(--s2);border-radius:3px;padding:5px 7px">
            <div style="font-family:var(--M);font-size:8px;color:var(--muted)">FLOW BBM</div>
            <div style="font-family:var(--M);font-size:11px;font-weight:600;color:var(--txt)">${r.flow_bbm} L/h</div>
          </div>
        </div>
        <div style="display:flex;align-items:center;gap:6px;padding:0 2px">
          <div style="font-family:var(--M);font-size:8px;color:var(--dim);flex-shrink:0">FASE:</div>
          <div style="font-family:var(--M);font-size:9px;font-weight:700;color:${phaseColors[phaseIdx]}">${r.phase}</div>
          <div style="flex:1"></div>
          <div style="font-family:var(--M);font-size:8px;color:var(--dim)">LOAD: <span style="color:${r.load>85?'var(--wa)':'var(--txt)'};font-weight:600">${r.load}%</span></div>
          <div style="font-family:var(--M);font-size:8px;color:var(--dim)">DUR: <span style="color:var(--in);font-weight:600">${r.test_dur}</span></div>
        </div>
      </div>`;
    } else if(isPit){
      const pct=r.target_pressure>0?Math.round(r.test_pressure/r.target_pressure*100):0;
      const pbar_col=pct>=100?'var(--ok)':pct>80?'var(--amber)':'var(--in)';
      const maxSt=r.max_st==='RUNNING'?'ok':r.max_st==='STANDBY'?'sb':'cr';
      h+=`<div class="rm-row" style="flex-direction:column;align-items:stretch;gap:6px">
        <div style="display:flex;align-items:center;gap:8px;cursor:pointer" onclick="openRoomPanel('${r.id}','${id}')">
          <div class="rm-dot" style="background:${r.col}"></div>
          <div class="rm-info">
            <div class="rm-name">${r.nm}</div>
            <div class="rm-type">${r.tp} · <span style="color:var(--muted)">${r.test_id}</span></div>
          </div>
          <div class="rm-st ${stCl}">${r.st}</div>
          <i class="ti ti-chevron-right" style="font-size:12px;color:var(--muted);flex-shrink:0"></i>
        </div>
        <!-- Pressure gauge -->
        <div style="background:var(--s2);border-radius:4px;padding:8px 10px">
          <div style="display:flex;justify-content:space-between;margin-bottom:5px">
            <div style="font-family:var(--M);font-size:8px;color:var(--dim)">TEST PRESSURE</div>
            <div style="font-family:var(--M);font-size:8px;color:var(--dim)">TARGET: <span style="color:var(--txt);font-weight:600">${r.target_pressure} bar</span></div>
          </div>
          <div style="font-family:var(--M);font-size:20px;font-weight:700;color:${pbar_col};line-height:1">${r.test_pressure} <span style="font-size:10px;font-weight:400;color:var(--dim)">bar</span></div>
          <div style="height:5px;background:var(--b1);border-radius:3px;margin-top:6px;overflow:hidden">
            <div style="height:100%;width:${Math.min(pct,100)}%;background:${pbar_col};border-radius:3px;transition:width 1s"></div>
          </div>
          <div style="display:flex;justify-content:space-between;margin-top:3px">
            <div style="font-family:var(--M);font-size:8px;color:var(--dim)">FASE: <span style="color:${r.phase==='HOLDING'?'var(--amber)':r.phase==='IDLE'?'var(--dim)':'var(--in)'};font-weight:600">${r.phase}</span></div>
            <div style="font-family:var(--M);font-size:8px;color:var(--dim)">${pct}%</div>
          </div>
        </div>
        <!-- Maximator -->
        <div style="background:var(--s2);border-radius:4px;padding:6px 10px">
          <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:5px">
            <div style="font-family:var(--M);font-size:8px;color:var(--dim);letter-spacing:1.5px">MAXIMATOR ${r.max_model}</div>
            <div class="rm-st ${maxSt}" style="font-size:8px">${r.max_st}</div>
          </div>
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:4px">
            <div>
              <div style="font-family:var(--M);font-size:8px;color:var(--muted)">INLET</div>
              <div style="font-family:var(--M);font-size:11px;font-weight:600;color:var(--txt)">${r.max_inlet} bar</div>
            </div>
            <div>
              <div style="font-family:var(--M);font-size:8px;color:var(--muted)">OUTLET</div>
              <div style="font-family:var(--M);font-size:11px;font-weight:600;color:${pbar_col}">${r.max_outlet} bar</div>
            </div>
          </div>
        </div>
        <!-- Flow & Fluid -->
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:4px">
          <div style="background:var(--s2);border-radius:3px;padding:5px 7px">
            <div style="font-family:var(--M);font-size:8px;color:var(--muted)">FLOW IN</div>
            <div style="font-family:var(--M);font-size:11px;font-weight:600;color:var(--txt)">${r.flow_in} <span style="font-size:8px">L/m</span></div>
          </div>
          <div style="background:var(--s2);border-radius:3px;padding:5px 7px">
            <div style="font-family:var(--M);font-size:8px;color:var(--muted)">FLUID</div>
            <div style="font-family:var(--M);font-size:11px;font-weight:600;color:var(--txt)">${r.fluid}</div>
          </div>
          <div style="background:var(--s2);border-radius:3px;padding:5px 7px">
            <div style="font-family:var(--M);font-size:8px;color:var(--muted)">T.FLUID</div>
            <div style="font-family:var(--M);font-size:11px;font-weight:600;color:var(--txt)">${r.fluid_temp}°C</div>
          </div>
        </div>
      </div>`;
    }
  });
  document.getElementById('pane-r').innerHTML=h;
}

/* ══ RENDER EVENTS ══ */
function renderEvents(id){
  const d=DATA[id];
  let h=`<div class="rp-sec">LOG TERBARU</div>`;
  d.events.forEach(e=>{
    h+=`<div class="ev-row">
      <div class="ev-t">${e.t}</div>
      <div class="ev-d ${e.c}"></div>
      <div class="ev-m">${e.m}</div>
    </div>`;
  });
  document.getElementById('pane-e').innerHTML=h;
}

/* ══ RENDER UTILITAS ══ */
function renderUtil(){
  const u=UTIL;
  const p1=u.pam.pump1, p2=u.pam.pump2;
  const lvl=u.pam.tank_level;
  const lvlCol=lvl<20?'var(--cr)':lvl<40?'var(--wa)':'var(--ok)';
  const pwrSt=u.listrik.st==='NORMAL'?'ok':'wa';
  document.getElementById('pane-u').innerHTML=`
  <!-- MOTOR PAM -->
  <div class="rp-sec">MOTOR PAM</div>
  <div class="util-card">
    <div class="util-card-hdr">
      <i class="ti ti-droplet util-card-ico" style="color:var(--in)"></i>
      <div class="util-card-title">${p1.nm}</div>
      <div class="rm-st ok">${p1.st}</div>
    </div>
    <div class="util-card-body">
      <div class="util-data-row"><span class="util-data-lbl">FLOW RATE</span><span class="util-data-val">${p1.flow} L/min</span></div>
      <div class="util-data-row"><span class="util-data-lbl">TEKANAN</span><span class="util-data-val">${p1.pres} bar</span></div>
      <div class="util-data-row"><span class="util-data-lbl">ARUS MOTOR</span><span class="util-data-val ${p1.amp>20?'wa':'ok'}">${p1.amp} A</span></div>
      <div class="util-data-row"><span class="util-data-lbl">SUHU MOTOR</span><span class="util-data-val ${p1.temp_motor>75?'wa':'ok'}">${p1.temp_motor}°C</span></div>
      <div class="util-data-row"><span class="util-data-lbl">JAM OPERASI</span><span class="util-data-val">${p1.run_hours} h</span></div>
    </div>
  </div>
  <div class="util-card">
    <div class="util-card-hdr">
      <i class="ti ti-droplet util-card-ico" style="color:var(--muted)"></i>
      <div class="util-card-title">${p2.nm}</div>
      <div class="rm-st sb">${p2.st}</div>
    </div>
    <div class="util-card-body">
      <div class="util-data-row"><span class="util-data-lbl">STATUS</span><span class="util-data-val ok">SIAP PAKAI</span></div>
      <div class="util-data-row"><span class="util-data-lbl">JAM OPERASI</span><span class="util-data-val">${p2.run_hours} h</span></div>
      <div class="util-data-row"><span class="util-data-lbl">SUHU MOTOR</span><span class="util-data-val ok">${p2.temp_motor}°C</span></div>
    </div>
  </div>
  <!-- Level Tangki -->
  <div class="rp-sec">TANGKI AIR</div>
  <div class="util-card">
    <div class="util-card-body">
      <div class="util-data-row">
        <span class="util-data-lbl">LEVEL TANGKI</span>
        <span class="util-data-val" style="color:${lvlCol}">${lvl}%</span>
      </div>
      <div class="level-bar-wrap">
        <div class="level-bar-outer"><div class="level-bar-inner" style="width:${lvl}%;background:${lvlCol}"></div></div>
        <div class="level-bar-pct" style="color:${lvlCol}">${lvl}%</div>
      </div>
      <div class="util-data-row" style="margin-top:8px"><span class="util-data-lbl">TOTAL HARI INI</span><span class="util-data-val">${u.pam.total_flow_hari} L</span></div>
      <div class="util-data-row"><span class="util-data-lbl">SERVICE TERAKHIR</span><span class="util-data-val">${u.pam.last_service}</span></div>
    </div>
  </div>
  <!-- LISTRIK -->
  <div class="rp-sec">PANEL LISTRIK</div>
  <div class="util-card">
    <div class="util-card-hdr">
      <i class="ti ti-bolt util-card-ico" style="color:var(--ok)"></i>
      <div class="util-card-title">Monitor Daya</div>
      <div class="rm-st ${pwrSt}">${u.listrik.st}</div>
    </div>
    <div class="util-card-body">
      <div class="util-data-row"><span class="util-data-lbl">TOTAL DAYA</span><span class="util-data-val">${u.listrik.total_kw} kW</span></div>
      <div class="util-data-row"><span class="util-data-lbl">POWER FACTOR</span><span class="util-data-val ${u.listrik.pf>=0.85?'ok':'wa'}">${u.listrik.pf}</span></div>
      <div class="util-data-row"><span class="util-data-lbl">FREKUENSI</span><span class="util-data-val">${u.listrik.freq} Hz</span></div>
      <div style="height:1px;background:var(--b1);margin:8px 0"></div>
      <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:4px">
        ${['R','S','T'].map((ph,i)=>{
          const v=[u.listrik.volt_r,u.listrik.volt_s,u.listrik.volt_t][i];
          const a=[u.listrik.amp_r,u.listrik.amp_s,u.listrik.amp_t][i];
          return `<div style="background:var(--s2);border-radius:3px;padding:5px 7px;text-align:center">
            <div style="font-family:var(--M);font-size:8px;color:var(--muted)">PHASE ${ph}</div>
            <div style="font-family:var(--M);font-size:10px;font-weight:600;color:var(--txt)">${v}V</div>
            <div style="font-family:var(--M);font-size:9px;color:var(--dim)">${a}A</div>
          </div>`;
        }).join('')}
      </div>
      <div class="util-data-row" style="margin-top:8px"><span class="util-data-lbl">kWh HARI INI</span><span class="util-data-val">${u.listrik.kwh_hari} kWh</span></div>
    </div>
  </div>
  <!-- HVAC -->
  <div class="rp-sec">HVAC / AC</div>
  ${[u.hvac.unit1,u.hvac.unit2,u.hvac.unit3].map(ac=>`
  <div class="util-card" style="margin-bottom:6px">
    <div class="util-card-hdr">
      <i class="ti ti-air-conditioning util-card-ico" style="color:var(--wa)"></i>
      <div class="util-card-title">${ac.nm}</div>
      <div class="rm-st ok">${ac.st}</div>
    </div>
    <div class="util-card-body">
      <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:4px">
        <div style="background:var(--s2);border-radius:3px;padding:5px 7px;text-align:center">
          <div style="font-family:var(--M);font-size:8px;color:var(--muted)">SET</div>
          <div style="font-family:var(--M);font-size:12px;font-weight:600;color:var(--in)">${ac.set}°C</div>
        </div>
        <div style="background:var(--s2);border-radius:3px;padding:5px 7px;text-align:center">
          <div style="font-family:var(--M);font-size:8px;color:var(--muted)">AKTUAL</div>
          <div style="font-family:var(--M);font-size:12px;font-weight:600;color:${ac.actual>ac.set+1?'var(--wa)':'var(--ok)'}">${ac.actual}°C</div>
        </div>
        <div style="background:var(--s2);border-radius:3px;padding:5px 7px;text-align:center">
          <div style="font-family:var(--M);font-size:8px;color:var(--muted)">ARUS</div>
          <div style="font-family:var(--M);font-size:12px;font-weight:600;color:var(--txt)">${ac.amp}A</div>
        </div>
      </div>
    </div>
  </div>`).join('')}
  <div style="font-family:var(--M);font-size:9px;color:var(--dim);margin-top:4px">
    Filter: <span style="color:var(--ok);font-weight:600">${u.hvac.filter_st}</span> · 
    Service berikutnya: <span style="color:var(--in)">${u.hvac.next_service}</span>
  </div>`;
}

/* ══ TABS ══ */
function swTab(n){
  ['s','l','r','e','u','m','p'].forEach(t=>{
    const tab=document.getElementById('tab-'+t);
    const pane=document.getElementById('pane-'+t);
    if(tab) tab.classList.remove('on');
    if(pane) pane.style.display='none';
  });
  const activeTab=document.getElementById('tab-'+n);
  const activePane=document.getElementById('pane-'+n);
  if(activeTab) activeTab.classList.add('on');
  if(activePane) activePane.style.display='block';
}

/* ══ ALARM SIDEBAR ══ */
function getAlarms(id){
  const d=DATA[id];const al=[];
  if(d.temp>26) al.push({lv:'cr',nm:`${d.nm}: Suhu ${d.temp}°C`,sub:'Melewati batas 26°C',id,time:getNow()});
  else if(d.temp>24) al.push({lv:'wa',nm:`${d.nm}: Suhu ${d.temp}°C`,sub:'Mendekati batas',id,time:getNow()});
  if(d.hum>60) al.push({lv:'wa',nm:`${d.nm}: Kelembaban ${d.hum}%`,sub:'Di atas normal',id,time:getNow()});
  if(d.pwr>90) al.push({lv:'wa',nm:`${d.nm}: Daya ${d.pwr}%`,sub:'Beban tinggi',id,time:getNow()});
  d.locks.filter(l=>l.st==='unlocked').forEach(l=>
    al.push({lv:'wa',nm:`${d.nm}: ${l.name}`,sub:'Tidak terkunci',id,time:l.time}));
  // Alarm dari room
  d.rooms.forEach(r=>{
    if(r.tp==='TEST CELL'){
      if(r.temp_oli>80) al.push({lv:'wa',nm:`${r.nm}: Suhu Oli ${r.temp_oli}°C`,sub:'Mendekati batas 85°C',id,time:getNow()});
      if(r.temp_coolant>90) al.push({lv:'wa',nm:`${r.nm}: Suhu Coolant ${r.temp_coolant}°C`,sub:'Mendekati batas',id,time:getNow()});
    }
    if(r.tp==='TEST PIT'){
      if(r.test_pressure>0&&r.test_pressure>=r.target_pressure)
        al.push({lv:'in',nm:`${r.nm}: Target Pressure Tercapai`,sub:`${r.test_pressure}/${r.target_pressure} bar`,id,time:getNow()});
    }
  });
  return al;
}
function getNow(){return new Date().toLocaleTimeString('en-GB',{hour:'2-digit',minute:'2-digit'});}

function updateAlarmSidebar(){
  const allAl=[...getAlarms('cr1'),...getAlarms('cr2'),...getAlarms('cr3')];
  const count=allAl.length;
  document.getElementById('al-count').textContent=count;
  document.getElementById('al-count').className='bc '+(count>0?allAl.some(a=>a.lv==='cr')?'cr':'wa':'ok');
  const list=document.getElementById('al-list');
  if(!count){
    list.innerHTML=`<div style="font-family:var(--M);font-size:8px;color:var(--dim);padding:4px 0;display:flex;align-items:center;gap:6px">
      <i class="ti ti-circle-check" style="color:var(--ok);font-size:12px"></i>Tidak ada alarm</div>`;
  } else {
    list.innerHTML=allAl.map(a=>`
      <div class="al-row" onclick="openPanel('${a.id}')">
        <div class="al-bar ${a.lv}"></div>
        <div class="al-txt"><div class="al-name">${a.nm}</div><div class="al-sub">${a.sub}</div></div>
        <div class="al-time">${a.time}</div>
      </div>`).join('');
  }
  ['cr1','cr2','cr3'].forEach(k=>{
    const al=getAlarms(k);
    const hasCr=al.some(a=>a.lv==='cr'),hasWa=al.some(a=>a.lv==='wa');
    const badge=document.getElementById('nb-'+k);
    if(hasCr){badge.className='cr-badge cr';badge.textContent='ALARM';}
    else if(hasWa){badge.className='cr-badge wa';badge.textContent='WARN';}
    else{badge.className='cr-badge ok';badge.textContent='OK';}
  });
  // Update header KPI
  document.getElementById('hkpi-alarm').textContent=count;
  document.getElementById('hkpi-alarm-dot').style.background=count>0?allAl.some(a=>a.lv==='cr')?'var(--cr)':'var(--wa)':'var(--ok)';
  // Hitung test aktif
  let active=0;
  Object.values(DATA).forEach(d=>d.rooms.forEach(r=>{if(r.st==='ACTIVE'||r.st==='OPERATIONAL')active++;}));
  document.getElementById('hkpi-active').textContent=active;
}

function updateUtilSidebar(){
  document.getElementById('sb-pam-val').textContent=`${UTIL.pam.pump1.flow.toFixed(1)} L/min · ${UTIL.pam.pump1.pres.toFixed(1)} bar`;
  document.getElementById('sb-pwr-val').textContent=`${UTIL.listrik.total_kw.toFixed(1)} kW · PF ${UTIL.listrik.pf.toFixed(2)}`;
  document.getElementById('hkpi-pwr').textContent=`${UTIL.listrik.total_kw.toFixed(0)} kW`;
}

/* ══ SIMULASI LIVE ══ */
function tickRoom(id){
  const d=DATA[id];
  d.temp=+(d.temp+(Math.random()-.5)*.25).toFixed(1);
  d.hum=Math.max(40,Math.min(76,Math.round(d.hum+(Math.random()-.5))));
  d.pwr=+(d.pwr+(Math.random()-.5)*.4).toFixed(1);
  d.freq=+(49.8+Math.random()*.4).toFixed(1);
  d.pf=+(Math.min(0.99,Math.max(0.82,d.pf+(Math.random()-.5)*.01))).toFixed(2);
  d.rooms.forEach(r=>{
    r.temp=+(r.temp+(Math.random()-.5)*.2).toFixed(1);
    r.hum=Math.max(40,Math.min(76,Math.round(r.hum+(Math.random()-.5))));
    if(r.tp==='TEST CELL'){
      r.rpm=Math.max(800,Math.round(r.rpm+(Math.random()-.5)*20));
      r.torque=+(r.torque+(Math.random()-.5)*2).toFixed(1);
      r.power_out=+(r.power_out+(Math.random()-.5)*.5).toFixed(1);
      r.temp_oli=+(r.temp_oli+(Math.random()-.5)*.3).toFixed(1);
      r.temp_coolant=+(r.temp_coolant+(Math.random()-.5)*.4).toFixed(1);
      r.flow_bbm=+(r.flow_bbm+(Math.random()-.5)*.2).toFixed(1);
      if(r.load!=null) r.load=+(r.load+(Math.random()-.5)*.5).toFixed(1);
    }
    if(r.tp==='TEST PIT'&&r.max_st==='RUNNING'){
      if(r.test_pressure<r.target_pressure)
        r.test_pressure=+(Math.min(r.target_pressure,r.test_pressure+r.pressure_rate*0.05)).toFixed(1);
      r.max_outlet=r.test_pressure;
      r.flow_in=+(r.flow_in+(Math.random()-.5)*.5).toFixed(1);
      r.fluid_temp=+(r.fluid_temp+(Math.random()-.5)*.1).toFixed(1);
    }
  });
}
function tickUtil(){
  UTIL.pam.pump1.flow=+(UTIL.pam.pump1.flow+(Math.random()-.5)*2).toFixed(1);
  UTIL.pam.pump1.pres=+(UTIL.pam.pump1.pres+(Math.random()-.5)*.1).toFixed(1);
  UTIL.pam.pump1.amp=+(UTIL.pam.pump1.amp+(Math.random()-.5)*.3).toFixed(1);
  UTIL.pam.pump1.temp_motor=+(UTIL.pam.pump1.temp_motor+(Math.random()-.5)*.2).toFixed(1);
  UTIL.listrik.total_kw=+(UTIL.listrik.total_kw+(Math.random()-.5)*3).toFixed(1);
  UTIL.listrik.pf=+(Math.min(0.99,Math.max(0.82,UTIL.listrik.pf+(Math.random()-.5)*.01))).toFixed(2);
  UTIL.listrik.amp_r=+(UTIL.listrik.amp_r+(Math.random()-.5)*.5).toFixed(1);
  UTIL.listrik.amp_s=+(UTIL.listrik.amp_s+(Math.random()-.5)*.5).toFixed(1);
  UTIL.listrik.amp_t=+(UTIL.listrik.amp_t+(Math.random()-.5)*.5).toFixed(1);
  UTIL.hvac.unit1.actual=+(UTIL.hvac.unit1.actual+(Math.random()-.5)*.1).toFixed(1);
  UTIL.hvac.unit2.actual=+(UTIL.hvac.unit2.actual+(Math.random()-.5)*.1).toFixed(1);
  UTIL.hvac.unit3.actual=+(UTIL.hvac.unit3.actual+(Math.random()-.5)*.1).toFixed(1);
}
setInterval(()=>{
  ['cr1','cr2','cr3'].forEach(tickRoom);
  tickUtil();
  updateAlarmSidebar();
  updateUtilSidebar();
  if(curId&&panelMode==='cr'){renderSensor(curId);renderRooms(curId);}
},4000);

/* ══ MOBILE ══ */
function toggleSidebar(){
  const sb=document.getElementById('lsb'),ov=document.getElementById('overlay');
  const open=sb.classList.toggle('open');
  ov.style.display='block';
  setTimeout(()=>ov.style.opacity=open?'1':'0',10);
  if(!open)setTimeout(()=>ov.style.display='none',300);
}
function closeSidebar(){
  document.getElementById('lsb').classList.remove('open');
  const ov=document.getElementById('overlay');
  ov.style.opacity='0';
  setTimeout(()=>ov.style.display='none',300);
}

/* ══ CLOCK ══ */
(function tick(){
  const n=new Date(),p=v=>String(v).padStart(2,'0');
  document.getElementById('clk').textContent=`${p(n.getHours())}:${p(n.getMinutes())}:${p(n.getSeconds())}`;
  setTimeout(tick,1000);
})();

/* ══ ANIMATION LOOP ══ */
const TMP=new THREE.Vector3();
let T=0;
function animate(){
  requestAnimationFrame(animate);
  T+=0.016;
  BODIES.forEach(b=>{
    const isH=hov===b.userData.id;
    const isSel=curId&&DATA[curId]&&(
      b.userData.id===curId||DATA[curId].rooms.find(r=>r.id===b.userData.id)
    );
    const ms=Array.isArray(b.material)?b.material:[b.material];
    ms.forEach(m=>{
      if(m.emissive&&m._base===undefined) m._base=m.emissiveIntensity;
      if(m.emissive){
        if(isSel)      m.emissiveIntensity=(m._base||0)+.28+Math.sin(T*3)*.1;
        else if(isH)   m.emissiveIntensity=(m._base||0)+.18;
        else           m.emissiveIntensity=m._base||0;
      }
    });
  });

  LBLS.forEach(lb=>{
    TMP.copy(lb.anchor).project(cam);
    if(TMP.z>=1){lb.el.style.opacity='0';lb._vis=false;return;}
    lb._vis=true;
    lb._sx=(TMP.x*.5+.5)*innerWidth;
    lb._sy=(-.5*TMP.y+.5)*innerHeight;
    lb._lx=lb._sx+lb.ox;
    lb._ly=lb._sy+lb.oy;
    lb.el.style.opacity='1';
    lb.el.style.left=lb._lx+'px';
    lb.el.style.top=lb._ly+'px';
    lb.el.style.transform='translate(-50%,-50%)';
  });

  svgEl.setAttribute('width',innerWidth);
  svgEl.setAttribute('height',innerHeight);
  svgEl.innerHTML=LBLS.map(lb=>{
    if(!lb._vis||!lb._sx) return '';
    const ew=(lb.el.offsetWidth||80)/2;
    const ex=lb._lx+(lb._sx>lb._lx?ew:-ew);
    const ey=lb._ly;
    let points;
    if(lb.by!==0&&lb.by!==undefined){
      const midY=lb._sy+lb.by,midX=ex;
      points=`${lb._sx},${lb._sy} ${lb._sx},${midY} ${midX},${midY} ${ex},${ey}`;
    } else {
      points=`${lb._sx},${lb._sy} ${ex},${lb._sy} ${ex},${ey}`;
    }
    return `
      <polyline points="${points}" fill="none" stroke="#2d4a6a" stroke-width="1.2" opacity="0.85"/>
      <circle cx="${lb._sx}" cy="${lb._sy}" r="2.5" fill="#4a6080" opacity="0.85"/>
      <circle cx="${ex}" cy="${ey}" r="1.5" fill="#4a6080" opacity="0.6"/>`;
  }).join('');

  renderer.render(scene,cam);
}
animate();

window.addEventListener('resize',()=>{
  const A=getA();
  cam.left=-Z*A;cam.right=Z*A;cam.top=Z;cam.bottom=-Z;
  cam.updateProjectionMatrix();
  renderer.setSize(innerWidth,innerHeight);
});

updateAlarmSidebar();
updateUtilSidebar();
buildSidebarRooms();

let stream = null;

async function showCamera() {
  const camView = document.getElementById('camera-view');
  const video = document.getElementById('camera');

  // pastikan panel kamera terlihat
  camView.style.display = "flex";

  // jika kamera sudah aktif, jangan buka lagi
  if (stream) {
    video.srcObject = stream;
    return;
  }

  try {
    stream = await navigator.mediaDevices.getUserMedia({
      video: { facingMode: "user" }
    });

    video.srcObject = stream;

  } catch (err) {
    console.error("Error membuka kamera:", err);
    alert("Kamera tidak bisa diakses!");
  }
}

function stopCamera() {
  if (stream) {
    stream.getTracks().forEach(t => t.stop());
    stream = null;
  }

  document.getElementById('camera-view').style.display = "none";
}

</script>
</body>
</html>