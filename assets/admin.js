/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import 'simplebar';
import 'simplebar/dist/simplebar.css';

import './huro/scripts/functions';
import './huro/scripts/main';
import './huro/scripts/components';
import './huro/scripts/popover';
import './huro/scripts/custom';

import './huro/styles/app.css';
import './huro/styles/main.css';

import './UiBundle/app.css';

//const $ = require('jquery');
//global.$ = global.jQuery = $;

require('./helpers/visible');


const feather = require('feather-icons');
feather.replace();

window.feather = feather;

// start the Stimulus application
import './stimulus_bootstrap.js';

