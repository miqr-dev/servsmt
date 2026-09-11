import _ from 'lodash';
import $ from 'jquery';
import Popper from 'popper.js';
import axios from 'axios';

window._ = _;
window.$ = window.jQuery = $;
window.Popper = Popper;
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

import 'bootstrap';
