import * as $ from 'jquery'
import * as functions from './functions'
import { Notyf } from 'notyf';
import 'notyf/notyf.min.css';
import bulmaCalendar from 'bulma-calendar';
import lightGallery from 'lightgallery';

import Choices from "choices.js";

import noUiSlider from 'nouislider';

"use strict";
export var notyf;
$(document).ready((function () {
    if (notyf = new Notyf({
        duration: 2e3,
        position: {x: "right", y: "bottom"},
        types: [{
            type: "warning",
            background: functions.themeColors.warning,
            icon: {className: "fas fa-hand-paper", tagName: "i", text: ""}
        }, {
            type: "info",
            background: functions.themeColors.info,
            icon: {className: "fas fa-info-circle", tagName: "i", text: ""}
        }, {
            type: "primary",
            background: functions.themeColors.primary,
            icon: {className: "fas fa-car-crash", tagName: "i", text: ""}
        }, {
            type: "accent",
            background: functions.themeColors.accent,
            icon: {className: "fas fa-car-crash", tagName: "i", text: ""}
        }, {
            type: "purple",
            background: functions.themeColors.purple,
            icon: {className: "fas fa-check", tagName: "i", text: ""}
        }, {
            type: "blue",
            background: functions.themeColors.blue,
            icon: {className: "fas fa-check", tagName: "i", text: ""}
        }, {
            type: "green",
            background: functions.themeColors.green,
            icon: {className: "fas fa-check", tagName: "i", text: ""}
        }, {type: "orange", background: functions.themeColors.orange, icon: {className: "fas fa-check", tagName: "i", text: ""}}]
    }), $(".toast-trigger").length && ($("#success-toast-demo").on("click", (function () {
        notyf.success("Your changes have been successfully saved!")
    })), $("#error-toast-demo").on("click", (function () {
        notyf.error("Looks like something went wrong, try again later.")
    })), $("#info-toast-demo").on("click", (function () {
        notyf.open({type: "info", message: "This is some useful information that you might need."})
    })), $("#warning-toast-demo").on("click", (function () {
        notyf.open({type: "warning", message: "Please be careful when driving back to home."})
    })), $("#purple-toast-demo").on("click", (function () {
        notyf.open({type: "purple", message: "This is a nice looking purple toast notification."})
    })), $("#blue-toast-demo").on("click", (function () {
        notyf.open({type: "blue", message: "This is a nice looking blue toast notification."})
    })), $("#green-toast-demo").on("click", (function () {
        notyf.open({type: "green", message: "This is a nice looking green toast notification."})
    })), $("#orange-toast-demo").on("click", (function () {
        notyf.open({type: "orange", message: "This is a nice looking orange toast notification."})
    })), $("#primary-toast-demo").on("click", (function () {
        $("body").hasClass("is-dark") ? notyf.open({
            type: "accent",
            message: "Please be careful when driving back to home."
        }) : notyf.open({type: "primary", message: "Please be careful when driving back to home."})
    }))),$("#lightgallery").length && lightGallery(document.getElementById("lightgallery")), $("#videogallery").length && lightGallery(document.getElementById("videogallery")));

    if ($("#bulma-datepicker-1").length && bulmaCalendar.attach("#bulma-datepicker-1", {
        color: functions.themeColors.primary,
        lang: "en"
    }), $("#bulma-datepicker-2").length && bulmaCalendar.attach("#bulma-datepicker-2", {
        displayMode: "dialog",
        startDate: new Date("02/11/2018"),
        minDate: "01/01/2018",
        maxDate: "12/31/2018",
        color: functions.themeColors.primary,
        lang: "en"
    }), $("#bulma-datepicker-3").length && bulmaCalendar.attach("#bulma-datepicker-3", {
        displayMode: "inline",
        startDate: new Date("02/11/2018"),
        minDate: "01/01/2018",
        maxDate: "12/31/2018",
        color: functions.themeColors.primary,
        lang: "en"
    }), $("#bulma-datepicker-4").length && bulmaCalendar.attach("#bulma-datepicker-4", {
        color: functions.themeColors.primary,
        lang: "en"
    }), $("#bulma-datepicker-5").length && bulmaCalendar.attach("#bulma-datepicker-5", {
        color: functions.themeColors.primary,
        lang: "en"
    }), $("#bulma-datepicker-6").length && bulmaCalendar.attach("#bulma-datepicker-6", {
        color: functions.themeColors.primary,
        lang: "en"
    }), $("#bulma-datepicker-7").length && bulmaCalendar.attach("#bulma-datepicker-7", {
        color: functions.themeColors.primary,
        lang: "en"
    }), $("#choices-text-remove-button").length) new Choices(document.getElementById("choices-text-remove-button"), {
        delimiter: ",",
        editItems: !0,
        removeItemButton: !0
    });
    if ($("#choices-multiple-remove-button").length) new Choices("#choices-multiple-remove-button", {removeItemButton: !0});
    if ($("#autocomplete-demo-simple").length) {
        $("#autocomplete-demo-simple").easyAutocomplete({
            url: "assets/data/user.json",
            getValue: "name",
            template: {
                type: "custom", method: function (e, t) {
                    return '\n                        <div class="template-wrapper">\n                            <div class="entry-text">\n                                <span>' + e + "</span>\n                            </div>\n                        </div>\n                    "
                }
            },
            highlightPhrase: !1,
            list: {
                maxNumberOfElements: 5, showAnimation: {
                    type: "fade", time: 400, callback: function () {
                    }
                }, match: {enabled: !0}, onChooseEvent: function () {
                }
            }
        })
    }
    if ($("#autocomplete-demo-subtext").length) {
        $("#autocomplete-demo-subtext").easyAutocomplete({
            url: "assets/data/user.json",
            getValue: "name",
            template: {
                type: "custom", method: function (e, t) {
                    return '\n                        <div class="template-wrapper">\n                            <div class="entry-text">\n                                <span>' + e + "</span>\n                                <span>" + t.location + "</span>\n                            </div>\n                        </div>\n                    "
                }
            },
            highlightPhrase: !1,
            list: {
                maxNumberOfElements: 5, showAnimation: {
                    type: "fade", time: 400, callback: function () {
                    }
                }, match: {enabled: !0}, onChooseEvent: function () {
                }
            }
        })
    }
    if ($("#autocomplete-demo-advanced").length) {
        $("#autocomplete-demo-advanced").easyAutocomplete({
            url: "assets/data/user.json",
            getValue: "name",
            template: {
                type: "custom", method: function (e, t) {
                    return '\n                        <div class="template-wrapper">\n                            <div class="avatar-wrapper">\n                                <img class="autocpl-avatar" src="' + t.pic + '">\n                                <img class="avatar-badge" src="' + t.badge + '">\n                            </div>\n                            <div class="entry-text">\n                                <span>' + e + "</span>\n                                <span>" + t.location + "</span>\n                            </div>\n                        </div>\n                    "
                }
            },
            highlightPhrase: !1,
            list: {
                maxNumberOfElements: 5, showAnimation: {
                    type: "fade", time: 400, callback: function () {
                    }
                }, match: {enabled: !0}, onChooseEvent: function () {
                }
            }
        })
    }
    if ($(".noui-base-slider").length) for (var e = document.getElementsByClassName("noui-base-slider"), t = 0; t < e.length; t++) noUiSlider.create(e[t], {
        start: [10, 90],
        connect: !0,
        orientation: "horizontal",
        range: {min: 0, max: 100}
    });
    if ($(".noui-vertical-slider").length) {
        var o = document.getElementsByClassName("noui-vertical-slider");
        for (t = 0; t < o.length; t++) noUiSlider.create(o[t], {
            start: [10, 90],
            connect: !0,
            orientation: "vertical",
            range: {min: 0, max: 100}
        })
    }
    if ($("#noui-range-slider").length) {
        var a = document.getElementById("noui-range-slider");
        noUiSlider.create(a, {start: [20], connect: [!0, !1], range: {min: 0, max: 100}})
    }
    if ($(".noui-tooltip-slider").length) {
        var n = document.getElementsByClassName("noui-tooltip-slider");
        for (t = 0; t < n.length; t++) {
            var i = Math.floor(101 * Math.random());
            noUiSlider.create(n[t], {start: [i], connect: [!0, !1], tooltips: [!0], range: {min: 0, max: 100}})
        }
    }
    if ($("#summernote").length && $("#summernote").summernote({
        placeholder: "Hello stand alone ui",
        tabsize: 2,
        height: 250,
        minHeight: null,
        maxHeight: null,
        focus: !0,
        toolbar: [["style", ["style"]], ["font", ["bold", "underline", "clear"]], ["para", ["ul", "ol", "paragraph"]], ["table", ["table"]], ["insert", ["link", "picture", "video"]], ["view", ["codeview", "help"]]]
    }), $("#sun-editor").length) SUNEDITOR.create(document.getElementById("sun-editor") || "sun-editor", {
        width: "100%",
        height: 250,
        placeholder: "Write your text here..."
    });
    if ($("#sun-editor-balloon").length) SUNEDITOR.create(document.getElementById("sun-editor-balloon") || "sun-editor-balloon", {
        width: "100%",
        height: 250,
        placeholder: "Write your text here...",
        mode: "balloon-always"
    });
    if ($("#video-player").length) {
        var l = document.querySelector("audio[controls], video[controls]");
        new MediaPlayer(l, {
            prefix: "media",
            lang: {
                play: "play",
                pause: "pause",
                mute: "mute",
                unmute: "unmute",
                volume: "volume",
                currentTime: "current time",
                remainingTime: "remaining time",
                enterFullscreen: "enter fullscreen",
                leaveFullscreen: "leave fullscreen",
                download: "download"
            },
            svgs: {
                play: "#symbol-play",
                pause: "#symbol-pause",
                mute: "#symbol-mute",
                unmute: "#symbol-unmute",
                volume: "#symbol-volume",
                currentTime: "#symbol-currentTime",
                remainingTime: "#symbol-remainingTime",
                enterFullscreen: "#symbol-enterFullscreen",
                leaveFullscreen: "#symbol-leaveFullscreen",
                download: "#symbol-download"
            },
            timeDir: "ltr",
            volumeDir: "ltr"
        })
    }
    $("#alertify-demo-1").length && $("#alertify-demo-1").on("click", (function () {
        functions.initConfirm("Standard Alert", "Are you sure you want to perfom this action? You won't be able to recover or to revert it.", !1, !1, "Delete", "Cancel")
    })), $("#alertify-demo-2").length && $("#alertify-demo-2").on("click", (function () {
        functions.initConfirm("Custom Alert", "You can pass a callback function as a callback parameter to define what happens after the confirm button is clicked.", !1, !1, "Callback", "Cancel", (function (e) {
            alert("The callback was executed!")
        }))
    })), $(".circle-chart-wrapper").length && $(".circle-chart-wrapper").each((function () {
        var e = $(this), t = e.attr("data-completion");
        e.find(".circle-chart__circle").attr("stroke-dasharray", t + ",100")
    })), $("#show-demo-loaders").length && $("#show-demo-loaders").on("click", (function () {
        var e = $(this);
        e.addClass("is-loading no-click"), $(".has-loader").addClass("has-loader-active"), setTimeout((function () {
            $(".has-loader").removeClass("has-loader-active"), e.removeClass("is-loading no-click")
        }), 3e3)
    }))
}));
