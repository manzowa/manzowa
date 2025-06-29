const relativePath = '/openapi/openapi.yaml';
const baseUrl = window.location.origin; 
const absoluteUrl = new URL(relativePath, baseUrl);

window.onload = function() {
  window.ui = SwaggerUIBundle({
    url: absoluteUrl.href,
    dom_id: '#swagger-ui',
    deepLinking: true,
    presets: [
      SwaggerUIBundle.presets.apis,
      SwaggerUIStandalonePreset
    ],
    plugins: [
      SwaggerUIBundle.plugins.DownloadUrl
    ],
     layout: "BaseLayout"
  });
};
console.log(baseUrl);