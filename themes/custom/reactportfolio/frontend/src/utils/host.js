/** 
 * @file themes/custom/reactportfolio/frontend/src/utils/host.js
 */

function getEnvironment() {
  return (window.location.host === 'hainguyenphc.com') ? 'prod' : 'local';
}

function getHost() {
  return getEnvironment() === 'prod' ? 'https://hainguyenphc.com' : 'https://hainguyenphc.com.ddev.site';
}

export default getHost;
