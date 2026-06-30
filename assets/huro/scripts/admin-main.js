import * as $ from 'jquery';
import * as functions from './functions';

'use strict';

functions.initPageLoader();
$(document).ready(function () {
    functions.switchLayouts();
    if (functions.env === 'development') {
        functions.changeDemoImages();
    }
    functions.initBgImages();
    functions.updateSidebarNaver();
    functions.initMobileNavbar();
    functions.initMobileNavbarHamburger();

    if ($('.main-sidebar, .sidebar-block').length) {
        functions.initSidebar();
        if ($('[data-sidebar-open]').length) {
            functions.openSidebar();
        }
        if (
            window.matchMedia('(min-width: 768px)').matches
            && window.matchMedia('(max-width: 1024px)').matches
            && window.matchMedia('(orientation: landscape)').matches
        ) {
            functions.closeSidebarPanel();
            $('.main-sidebar, .sidebar-brand').removeClass('is-bordered');
        }
        $(window).on('resize', function () {
            if (
                window.matchMedia('(min-width: 768px)').matches
                && window.matchMedia('(max-width: 1024px)').matches
                && window.matchMedia('(orientation: landscape)').matches
            ) {
                functions.closeSidebarPanel();
                $('.main-sidebar, .sidebar-brand').removeClass('is-bordered');
            }
        });
    }

    functions.initCollapsibleMenu();
    functions.initDropdowns();
    functions.adjustDropdowns();
    functions.initHSelect();
    functions.initAdvancedFlexTable();
});
