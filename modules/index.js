module.exports={"ACTIVITY_SHORTCUTS":{name:"Activity Shortcuts",desc:"Registers activity shortcuts on the side menu",id:"ACTIVITY_SHORTCUTS",dependencies:{fn:["gh_url","svg"]},vars:{appends:[["sword-clash","MobList","Open Battles"],["fishing","Fishing","Open Fishing"],["log","Woodcutting","Open Woodcutting"],["metal-bar","Mining","Open Mining"],["stone-block","Stonecutting","Open Stonecutting"]]},load:function(e,n){var s,t=n.spec.vars,i=e("<a href='javascript:;' class='avi-tip avi-menu-shortcut' style='border-bottom:none'/>"),a=e("#navWrapper").find("ul");n.vars.li=e('<li class="avi-menu"/>');for(var o=0;o<t.appends.length;o++)s=i.clone().attr({"data-delegate-click":"#load"+t.appends[o][1],title:t.appends[o][2]}),n.vars.li.append(s),n.dependencies.fn.svg(s,n.dependencies.fn.gh_url("res/svg/"+t.appends[o][0]+".svg"));a.append(n.vars.li)},unload:function(e,n){n.vars.li.remove()}},"HOUSE_NOTIFICATIONS":{name:"House notifications",desc:"Creates toast & sound notifications when house construction and/or Harvestron finish",id:"HOUSE_NOTIFICATIONS",dependencies:{fn:["parseTimeStringLong","gh_url","notification"],classes:["AloTimer","Interval","SFX"]},settings:{desc:{"Construction sound":"Play a sound when construction finishes","Construction toast":"Display a toast when construction finishes"},defaults:{"Construction sound":!0,"Construction toast":!0},demo:{"Construction sound":function(n,e,s){s.vars.sfx.play()},"Construction toast":function(n,e,s){s.dependencies.fn.notification("Construction finished",s.spec.name)}}},funcs:{click_house:function(){document.getElementById("header_house").click()},notify:function(n){n.vars.notified||(console.info("Construction finished"),n.settings["Construction sound"]&&n.vars.sfx.play(),n.settings["Construction toast"]&&n.dependencies.fn.notification("Construction finished",n.spec.name,{onclick:n.spec.funcs.click_house})),n.vars.notified=!0}},load:function(n,e){function s(){n.ajax("/house.php",{global:!1}).done(function(n){"undefined"!=typeof n.m&&o(n.m)})}function o(n){var o=new e.dependencies.classes.Interval(e.spec.name);if(o.clear(),-1!==n.indexOf("available again")){var i=new e.dependencies.classes.AloTimer(e.dependencies.fn.parseTimeStringLong(n));o.set(function(){i.isFinished()?(o.clear(),e.spec.funcs.notify(e)):e.vars.notified=!1},1e3)}else-1!==n.indexOf("are available")?e.spec.funcs.notify(e):setTimeout(s,1e3)}e.vars={notified:!1,house_requery:function(n,e,s){-1!==s.url.indexOf("house")&&"undefined"!=typeof e.responseJSON&&"undefined"!=typeof e.responseJSON.m&&o(e.responseJSON.m)},sfx:new e.dependencies.classes.SFX(e.dependencies.fn.gh_url("res/sfx/circ_saw.wav"))},n(document).ajaxComplete(e.vars.house_requery),s()},unload:function(n,e){n(document).unbind("ajaxComplete",e.vars.house_requery)}},"HOUSE_TIMERS":{name:"House timers",desc:"Shows house construction timers without the need for an alarm clock",id:"HOUSE_TIMERS",dependencies:{fn:["parseTimeStringLong"],classes:["AloTimer","CssManager","Interval"]},load:function(e,a){function n(){e.ajax("/house.php",{global:!1}).done(function(e){"undefined"!=typeof e.m&&o(e.m)})}function s(s){s.clear(),a.vars.paneSpan.addClass("avi-highlight").html(e('<span data-delegate-click="#header_house" style="cursor:pointer;text-decoration:underline;padding-right:5px">Ready!</span>')).append(e("<a href='javascript:;'>(refresh)</a>").click(n)),a.applyGlobalHandlers(a.vars.paneSpan)}function o(e){var o=new a.dependencies.classes.Interval(a.spec.name);if(o.clear(),-1!==e.indexOf("available again")){var r=new a.dependencies.classes.AloTimer(a.dependencies.fn.parseTimeStringLong(e));o.set(function(){r.isFinished()?s(o):a.vars.paneSpan.removeClass("avi-highlight").text(r.toString())},1e3)}else-1!==e.indexOf("are available")?s(o):setTimeout(n,3e3)}var r=e("<div class='col-xs-6 col-md-12'/>");a.vars={paneLabel:r.clone().addClass("col-lg-5 gold").text("Construction:"),paneSpan:e("<span>House unavailable</span>"),house_requery:function(e,a,n){-1!==n.url.indexOf("house")&&"undefined"!=typeof a.responseJSON&&"undefined"!=typeof a.responseJSON.m&&o(a.responseJSON.m)},css:(new a.dependencies.classes.CssManager).setRules({"#constructionNotifier,#houseTimerTable [data-typeid='Construction']":{display:"none !important"}}).addToDOM()},a.vars.paneSpanContainer=r.clone().addClass("col-lg-7").html(a.vars.paneSpan),e("#houseTimerInfo").addClass("avi-force-block"),e("#houseTimerTable").prepend(a.vars.paneLabel,a.vars.paneSpanContainer),e(document).ajaxComplete(a.vars.house_requery),e.ajax("/house.php",{global:!1}).done(function(e){"undefined"!=typeof e.m&&o(e.m)})},unload:function(e,a){a.vars.paneLabel.remove(),a.vars.paneSpanContainer.remove(),a.vars.css.removeFromDOM(),e(document).unbind("ajaxComplete",a.vars.house_requery),e("#houseTimerInfo").removeClass("avi-force-block"),new a.dependencies.classes.Interval(a.spec.name).clear()}},"MARKET_TOOLTIPS":{name:"Market tooltips",desc:"Performs a market price lookup when you hover a supported item",id:"MARKET_TOOLTIPS",dependencies:{fn:["analysePrice","numberWithCommas","openMarket"],classes:["Request"]},vars:{CACHE_TTL:1/3600*60,html:'<table class="avi" style="margin:auto"><thead><tr><th colspan="3">Current market price (1st page)</th></tr><tr><th>Low</th><th>Average</th><th>High</th></tr></thead><tbody><tr data-id="prices"><td></td><td></td><td></td></tr></tbody></table>'},load:function(e,r){function t(e){var t=r.dependencies.fn.analysePrice(e.l);r.vars.dom.low_currency.text(r.dependencies.fn.numberWithCommas(t.low)),r.vars.dom.avg_currency.text(r.dependencies.fn.numberWithCommas(t.avg)),r.vars.dom.high_currency.text(r.dependencies.fn.numberWithCommas(t.high))}function n(){d.click(),r.dependencies.fn.openMarket("Ingredients")}function s(){const t=e(this),n=t.text().trim();"undefined"==typeof r.spec.vars.tradeskill_mats[n]?fn.notification("Failed to lookup "+n+": ID not found"):new r.dependencies.classes.Request("/market.php",r.spec.vars.CACHE_TTL).post({type:"ingredient",page:0,q:0,ll:0,hl:0,st:r.spec.vars.tradeskill_mats[n]}).done(function(n){const s=t.attr("aria-describedby"),a=e("#"+s);if(s&&a.length){const o=r.dependencies.fn.analysePrice(n.l),c=a.find("tr[data-id=prices]>td");c.first().text(r.dependencies.fn.numberWithCommas(o.low)).next().text(r.dependencies.fn.numberWithCommas(o.avg)).next().text(r.dependencies.fn.numberWithCommas(o.high))}})}function a(){const t=e(this),a=t.text().trim(),o=e("<span>"+a+"</span>");t.html(o),o.popover({title:a,html:!0,trigger:"hover",container:"body",viewport:{selector:"body",padding:0},placement:"auto right",content:e(r.spec.vars.html)}),o.mouseenter(s).css("cursor","pointer").click(n)}var o,c=e("#currencyTooltipMarketable"),i=e("#currencyTooltip"),d=e("#modalBackground");r.vars={dom:{},clickies:e("#allThemTables").find(".currencyWithTooltip:not(:contains(Gold))"),click:{currency:function(){const t=e(this).find(">td:first").text().trim();r.dependencies.fn.openMarket(t.substring(0,t.length-1))}},observers:{currency_tooltips:new MutationObserver(function(e){if(e.length&&c.is(":visible")){const n=c.attr("class"),s=n.replace("crystals","premium").replace("materials","weapon_scraps").replace("fragments","gem_fragments");r.vars.dom.row_currency.attr("class",n),"gold"===n?o.text("N/A"):(o.text(" "),new r.dependencies.classes.Request("/market.php",r.spec.vars.CACHE_TTL).post({type:"currency",page:0,st:s}).done(t))}}),inventory_table:new MutationObserver(function(r){for(var t=0;t<r.length;t++)if(r[t].addedNodes.length){for(var n=0;n<r[t].addedNodes.length;n++)if(r[t].addedNodes[n]instanceof HTMLTableSectionElement){const s=e(r[t].addedNodes[n]);s.find("th:contains(Ingredient)").length&&s.find(">tr>[data-th=Item]").each(a);break}break}})}},r.vars.clickies.css("cursor","pointer").click(r.vars.click.currency),r.vars.dom.table_currency=e(r.spec.vars.html),r.vars.dom.row_currency=r.vars.dom.table_currency.find("tr[data-id=prices]"),o=r.vars.dom.row_currency.find(">td"),r.vars.dom.low_currency=o.first(),r.vars.dom.avg_currency=r.vars.dom.low_currency.next(),r.vars.dom.high_currency=r.vars.dom.avg_currency.next(),i.append(r.vars.dom.table_currency),r.vars.observers.currency_tooltips.observe(i[0],{attributes:!0}),r.vars.observers.inventory_table.observe(document.querySelector("#inventoryTable"),{attributes:!0,childList:!0,characterData:!0})},unload:function(e,r){if(r.vars.clickies.css("cursor","initial").unbind("click",r.vars.click.currency),"undefined"!=typeof r.vars.dom)for(var t in r.vars.dom)r.vars.dom.hasOwnProperty(t)&&(r.vars.dom[t].remove(),delete r.vars.dom[t]);if("undefined"!=typeof r.vars.observers)for(var n in r.vars.observers)r.vars.observers.hasOwnProperty(n)&&r.vars.observers[n].disconnect()}}}