const fs = require('fs');
const code = fs.readFileSync('public/js/app.js', 'utf-8');
const jsdom = require('jsdom');
const { JSDOM } = jsdom;
const dom = new JSDOM(`<!DOCTYPE html><html><body><div id="mainViews"></div><div id="targetHarianTable"><tbody></tbody></div><div id="targetStartDate"></div><div id="targetCurrentDate"></div><div id="targetWorkingDays"></div><div id="filterTargetKecamatan"></div><div id="filterTargetRole"></div></body></html>`);
global.window = dom.window;
global.document = dom.window.document;
global.Chart = class { constructor() {} destroy() {} };

eval(code.replace(/document\.addEventListener.*?\);/s, ''));

try {
    processData([], { region: {}, user: {}, sls: {} });
    console.log("SUCCESS");
} catch(e) {
    console.log("ERROR:", e);
}
