import 'simplebar';
import 'simplebar/dist/simplebar.css';

import './huro/scripts/functions';
import './huro/scripts/admin-main';
import './huro/scripts/custom';

import './huro/styles/app.css';
import './huro/styles/main.css';

import './UiBundle/app.css';

require('./helpers/visible');

const feather = require('feather-icons');
feather.replace();

window.feather = feather;

import './stimulus_bootstrap.js';
