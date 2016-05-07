module.exports = {
    name: "Market tooltips",
    desc: "Performs a market price lookup when you hover a supported item",
    id: "MARKET_TOOLTIPS",
    dependencies: {
        fn: ["analysePrice", "numberWithCommas", "openMarket"],
        classes: ["Request"]
    },
    vars: {
        /**
         * How long the market data will live in cache (in hours)
         * @type Number
         */
        CACHE_TTL: 1 / 3600 * 60, //60 sec
        /**
         * Our tooltip HTML
         * @type String
         */
        html: '<table class="avi" style="margin:auto"><thead><tr><th colspan="3">Current market price (1st page)</th></tr><tr><th>Low</th><th>Average</th><th>High</th></tr></thead><tbody><tr data-id="prices"><td></td><td></td><td></td></tr></tbody></table>',
    },
    load: function ($, module) {
        /**
         * Ajax done handler for price lookups
         * @param {Object} r
         */
        function $done$currencyTooltip(r) {
            var analysis = module.dependencies.fn.analysePrice(r.l);

            module.vars.dom.low_currency.text(module.dependencies.fn.numberWithCommas(analysis.low));
            module.vars.dom.avg_currency.text(module.dependencies.fn.numberWithCommas(analysis.avg));
            module.vars.dom.high_currency.text(module.dependencies.fn.numberWithCommas(analysis.high));
        }

        /**
         * Click handler for ingredients
         */
        function $click$ingredient() {
            $modalBackground.click();
            module.dependencies.fn.openMarket("Ingredients");
        }

        /**
         * MouseEnter handler for ingredients in the inventory
         */
        function $mouseEnter$inventoryIngredients() {
            const $this = $(this),
                ingredient = $this.text().trim();

            if (typeof(module.spec.vars.tradeskill_mats[ingredient]) === "undefined") {
                fn.notification("Failed to lookup " + ingredient + ": ID not found");
            } else {
                (new module.dependencies.classes.Request("/market.php", module.spec.vars.CACHE_TTL))
                    .post({
                        type: "ingredient",
                        page: 0,
                        q: 0,
                        ll: 0,
                        hl: 0,
                        st: module.spec.vars.tradeskill_mats[ingredient]
                    }).done(function (r) {
                    const describedBy = $this.attr("aria-describedby"),
                        $describedBy = $("#" + describedBy);

                    if (describedBy && $describedBy.length) {
                        const analysis = module.dependencies.fn.analysePrice(r.l),
                            $tds = $describedBy.find("tr[data-id=prices]>td");

                        $tds
                            .first().text(module.dependencies.fn.numberWithCommas(analysis.low))
                            .next().text(module.dependencies.fn.numberWithCommas(analysis.avg))
                            .next().text(module.dependencies.fn.numberWithCommas(analysis.high));
                    }
                });
            }
        }

        /**
         * Each handler for inventory ingredients
         */
        function $each$inventoryTable() {
            const $this = $(this),
                ingredient = $this.text().trim(),
                $span = $('<span>' + ingredient + '</span>');
            $this.html($span);

            $span.popover({
                title: ingredient,
                html: true,
                trigger: "hover",
                container: "body",
                viewport: {"selector": "body", "padding": 0},
                placement: "auto right",
                content: $(module.spec.vars.html)
            });

            $span.mouseenter($mouseEnter$inventoryIngredients)
                .css("cursor", "pointer")
                .click($click$ingredient);
        }

        var $colourReference = $("#currencyTooltipMarketable"),
            $currencyTooltip = $("#currencyTooltip"),
            $modalBackground = $("#modalBackground"),
            $allTDs;

        module.vars = {
            dom: {},
            clickies: $("#allThemTables").find(".currencyWithTooltip:not(:contains(Gold))"),
            click: {
                currency: function () {
                    const type = $(this).find(">td:first").text().trim();
                    module.dependencies.fn.openMarket(type.substring(0, type.length - 1));
                }
            },
            observers: {
                currency_tooltips: new MutationObserver(
                    function (records) {
                        if (records.length && $colourReference.is(":visible")) {
                            const cssClass = $colourReference.attr("class"),
                                marketID = cssClass.replace("crystals", "premium")
                                    .replace("materials", "weapon_scraps")
                                    .replace("fragments", "gem_fragments");

                            module.vars.dom.row_currency.attr("class", cssClass);

                            if (cssClass === "gold") {
                                $allTDs.text("N/A");
                            } else {
                                $allTDs.text(" ");

                                (new module.dependencies.classes.Request("/market.php", module.spec.vars.CACHE_TTL)).post({
                                    type: "currency",
                                    page: 0,
                                    st: marketID
                                }).done($done$currencyTooltip);
                            }
                        }
                    }),
                inventory_table: new MutationObserver(
                    /** @param {MutationRecord[]} records */
                    function (records) {
                        for (var i = 0; i < records.length; i++) {
                            if (records[i].addedNodes.length) {
                                for (var n = 0; n < records[i].addedNodes.length; n++) {
                                    if (records[i].addedNodes[n] instanceof HTMLTableSectionElement) {
                                        const $tbody = $(records[i].addedNodes[n]);

                                        if ($tbody.find("th:contains(Ingredient)").length) { //Bingo!
                                            $tbody.find(">tr>[data-th=Item]").each($each$inventoryTable);
                                        }
                                        break;
                                    }
                                }
                                break;
                            }
                        }
                    }
                )
            }
        };

        module.vars.clickies.css("cursor", "pointer").click(module.vars.click.currency);

        module.vars.dom.table_currency = $(module.spec.vars.html);
        module.vars.dom.row_currency = module.vars.dom.table_currency.find("tr[data-id=prices]");
        $allTDs = module.vars.dom.row_currency.find(">td");
        module.vars.dom.low_currency = $allTDs.first();
        module.vars.dom.avg_currency = module.vars.dom.low_currency.next();
        module.vars.dom.high_currency = module.vars.dom.avg_currency.next();

        $currencyTooltip.append(module.vars.dom.table_currency);

        module.vars.observers.currency_tooltips.observe($currencyTooltip[0], {attributes: true});
        module.vars.observers.inventory_table.observe(document.querySelector("#inventoryTable"), {
            attributes: true,
            childList: true,
            characterData: true
        });
    },
    unload: function ($, module) {
        module.vars.clickies.css("cursor", "initial").unbind("click", module.vars.click.currency);
        if (typeof(module.vars.dom) !== "undefined") {
            for (var i in module.vars.dom) {
                if (module.vars.dom.hasOwnProperty(i)) {
                    module.vars.dom[i].remove();
                    delete module.vars.dom[i];
                }
            }
        }

        if (typeof(module.vars.observers) !== "undefined") {
            for (var j in module.vars.observers) {
                if (module.vars.observers.hasOwnProperty(j)) {
                    module.vars.observers[j].disconnect();
                }
            }
        }
    }
};