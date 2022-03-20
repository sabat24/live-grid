import * as $ from 'jquery'
import * as functions from './functions'

"use strict";
functions.initPageLoader(), $(document).ready((function () {
    functions.switchLayouts(),
    "development" === functions.env && functions.changeDemoImages(),
        functions.initBgImages(),
        functions.updateSidebarNaver(),
        functions.initMobileNavbar(),
        functions.initMobileNavbarHamburger(),
    $(".main-sidebar, .sidebar-block").length && (functions.initSidebar(), $("[data-sidebar-open]").length && functions.openSidebar(), window.matchMedia("(min-width: 768px)").matches && window.matchMedia("(max-width: 1024px)").matches && window.matchMedia("(orientation: landscape)").matches && (functions.closeSidebarPanel(), $(".main-sidebar, .sidebar-brand").removeClass("is-bordered")), $(window).on("resize", (function () {
        window.matchMedia("(min-width: 768px)").matches && window.matchMedia("(max-width: 1024px)").matches && window.matchMedia("(orientation: landscape)").matches && (functions.closeSidebarPanel(), $(".main-sidebar, .sidebar-brand").removeClass("is-bordered"))
    }))),
    $(".view-wrapper").hasClass("is-webapp") && functions.initWebapp(),
        functions.initCollapsibleMenu(),
        functions.initStuckHeader(),
        functions.initNavbarDropdowns(),
        functions.initDropdowns(),
        functions.initMobileDropdowns(),
        functions.adjustDropdowns(),
        functions.initChosenSelects(),
        functions.initTabs(),
        functions.initTabbedWidgets(),
        functions.initHSelect(),
        functions.initComboBox(),
        functions.initImageComboBox(),
        functions.initUserComboBox(),
        functions.initStackedComboBox(),
        functions.initBigComboBox(),
        functions.initAccordion(),
        functions.initAnimatedModals(),
        functions.initHModals(),
        functions.initPanels(),
        functions.initSmallTextTip(),
        functions.initTextTip(),
        functions.initMediumTextTip(),
        functions.initAnimatedCheckboxes(),
        functions.initCustomTextFilter(),
        functions.initTextFilter(),
        functions.initAdvancedFlexTable(),
        functions.initSingleAccordion(),
        functions.initCollapse(),
        functions.initPlayers(),
        functions.initSearch(),
        functions.initDarkMode()
}));
