import './bootstrap';
import '@fortawesome/fontawesome-free/css/all.min.css';
import Alpine from 'alpinejs';

window.Alpine = Alpine;

const shouldLoadEditorDeps = () => {
	return Boolean(
		document.querySelector('[data-rich-editor], #editor, [x-data*="editorComponent"]') ||
		window.location.pathname.includes('/documents/')
	);
};

const initializeEditorDependencies = async () => {
	const [
		{ default: Quill },
		{ default: hljs },
		{ default: hljsPhp },
		{ default: hljsJavascript },
		{ default: hljsJson },
		{ default: hljsXml },
		{ default: hljsCss },
		{ default: hljsBash },
		{ default: hljsSql },
		{ default: hljsMarkdown },
		{ default: ImageResize },
	] = await Promise.all([
		import('quill'),
		import('highlight.js/lib/core'),
		import('highlight.js/lib/languages/php'),
		import('highlight.js/lib/languages/javascript'),
		import('highlight.js/lib/languages/json'),
		import('highlight.js/lib/languages/xml'),
		import('highlight.js/lib/languages/css'),
		import('highlight.js/lib/languages/bash'),
		import('highlight.js/lib/languages/sql'),
		import('highlight.js/lib/languages/markdown'),
		import('quill-image-resize-module'),
		import('highlight.js/styles/atom-one-dark.css'),
	]);

	hljs.registerLanguage('php', hljsPhp);
	hljs.registerLanguage('javascript', hljsJavascript);
	hljs.registerLanguage('json', hljsJson);
	hljs.registerLanguage('xml', hljsXml);
	hljs.registerLanguage('css', hljsCss);
	hljs.registerLanguage('bash', hljsBash);
	hljs.registerLanguage('sql', hljsSql);
	hljs.registerLanguage('markdown', hljsMarkdown);

	Quill.register('modules/imageResize', ImageResize);
	window.Quill = Quill;
	window.hljs = hljs;
};

if (shouldLoadEditorDeps()) {
	await initializeEditorDependencies();
}

Alpine.start();
