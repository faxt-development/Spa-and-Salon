<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PGVector Test</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-2xl font-bold mb-4">PGVector Test</h1>

        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-xl font-semibold mb-4">Test Vector Operations</h2>
            <div class="mb-4 flex flex-wrap gap-2">
                <button id="testButton" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
                    Run Test
                </button>
                <button id="searchPathButton" class="bg-purple-500 hover:bg-purple-600 text-white px-4 py-2 rounded">
                    Check Search Path
                </button>
                <button id="viewEmbeddingButton" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded">
                    View Sample Embedding
                </button>
                <button id="schemaIdButton" class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded">
                    Test Schema ID
                </button>
                <button id="checkExtensionButton" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded">
                    Check Extension
                </button>
            </div>

            <div id="loading" class="hidden">
                <p class="text-gray-600">Testing pgvector functionality...</p>
            </div>

            <div id="results" class="hidden">
                <h3 class="text-lg font-medium mb-2">Results:</h3>
                <pre id="resultJson" class="bg-gray-100 p-4 rounded overflow-auto max-h-96"></pre>
            </div>

            <div id="error" class="hidden">
                <h3 class="text-lg font-medium mb-2 text-red-600">Error:</h3>
                <pre id="errorJson" class="bg-red-50 p-4 rounded overflow-auto max-h-96"></pre>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-xl font-semibold mb-4">Test Vector Syntax</h2>
            <div class="mb-4 flex flex-wrap gap-2">
                <button class="test-syntax bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded" data-syntax="::vector">
                    Test ::vector
                </button>
                <button class="test-syntax bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded" data-syntax="::public.vector">
                    Test ::public.vector
                </button>
                <button class="test-syntax bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded" data-syntax="::extensions.vector">
                    Test ::extensions.vector
                </button>
                <button class="test-syntax bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded" data-syntax="::pg_catalog.vector">
                    Test ::pg_catalog.vector
                </button>
                <button class="test-syntax bg-purple-500 hover:bg-purple-600 text-white px-4 py-2 rounded" data-syntax="">
                    Test No Cast
                </button>
            </div>
            <div class="mt-4">
                <input type="text" id="customSyntax" placeholder="Custom syntax (e.g., ::pgvector.vector)"
                       class="border rounded px-3 py-2 w-64">
                <button id="testCustom" class="bg-purple-500 hover:bg-purple-600 text-white px-4 py-2 rounded ml-2">
                    Test Custom
                </button>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-xl font-semibold mb-4">Test Raw SQL Query</h2>
            <div class="mb-4">
                <textarea id="rawSqlQuery" rows="4" class="border rounded px-3 py-2 w-full font-mono text-sm"
                    placeholder="Enter raw SQL query to test (e.g., SELECT '[1,2,3]'::extensions.vector <=> '[4,5,6]'::extensions.vector AS distance)">SELECT '[1,2,3]'::extensions.vector <=> '[4,5,6]'::extensions.vector AS distance</textarea>
            </div>
            <div class="mb-4">
                <button id="testRawSqlButton" class="bg-primary-500 hover:bg-primary-600 text-white px-4 py-2 rounded">
                    Execute Raw SQL
                </button>
            </div>
            <div class="mt-4">
                <h3 class="text-lg font-medium mb-2">Suggested Queries to Try:</h3>
                <ul class="list-disc pl-5 space-y-2 text-sm font-mono">
                    <li><a href="#" class="sql-example text-blue-600 hover:underline">SELECT '[1,2,3]'::extensions.vector <=> '[4,5,6]'::extensions.vector AS distance</a></li>
                    <li><a href="#" class="sql-example text-blue-600 hover:underline">SELECT '[1,2,3]'::"16388".vector <=> '[4,5,6]'::"16388".vector AS distance</a></li>
                    <li><a href="#" class="sql-example text-blue-600 hover:underline">SELECT vector_dims('[1,2,3]'::extensions.vector) AS dimensions</a></li>
                    <li><a href="#" class="sql-example text-blue-600 hover:underline">SELECT '[1,2,3]' <=> '[4,5,6]' AS distance</a></li>
                </ul>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-xl font-semibold mb-4">Test RPC Approach</h2>
            <div class="mb-4">
                <p class="text-gray-700 mb-4">
                    This test creates and tests a PostgreSQL function for vector similarity search that works around schema and search path issues.
                </p>
                <button id="testRpcButton" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded">
                    Test RPC Approach
                </button>
            </div>
            <div class="mt-4">
                <p class="text-sm text-gray-600">
                    <strong>Note:</strong> The RPC approach creates a database function that encapsulates vector operations,
                    avoiding the need for explicit vector casting in SQL queries.
                </p>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4">Schema Information</h2>
            <div class="space-y-2">
                <p>Based on your search path results:</p>
                <ul class="list-disc pl-5 space-y-1">
                    <li>Search path is set to: <code>public</code></li>
                    <li>Vector extension is installed in schema: <code>16388</code> (likely <code>extensions</code>)</li>
                </ul>
                <p class="mt-2">Try the different syntax options above to determine which one works with your Supabase configuration.</p>
                <p class="mt-2 text-sm text-gray-600">Note: The schema number 16388 typically corresponds to the <code>extensions</code> schema in Supabase.</p>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const testButton = document.getElementById('testButton');
            const searchPathButton = document.getElementById('searchPathButton');
            const viewEmbeddingButton = document.getElementById('viewEmbeddingButton');
            const schemaIdButton = document.getElementById('schemaIdButton');
            const checkExtensionButton = document.getElementById('checkExtensionButton');
            const loading = document.getElementById('loading');
            const results = document.getElementById('results');
            const resultJson = document.getElementById('resultJson');
            const error = document.getElementById('error');
            const errorJson = document.getElementById('errorJson');

            testButton.addEventListener('click', function() {
                runTest('/api/test-pgvector');
            });

            searchPathButton.addEventListener('click', function() {
                runTest('/api/test-pgvector/search-path');
            });

            viewEmbeddingButton.addEventListener('click', function() {
                runTest('/api/test-pgvector/embedding');
            });

            schemaIdButton.addEventListener('click', function() {
                runTest('/api/test-pgvector/schema-id');
            });

            checkExtensionButton.addEventListener('click', function() {
                runTest('/api/test-pgvector/check-extension');
            });

            document.querySelectorAll('.test-syntax').forEach(button => {
                button.addEventListener('click', function() {
                    const syntax = this.getAttribute('data-syntax');
                    runTest(`/api/test-pgvector/syntax?syntax=${encodeURIComponent(syntax)}`);
                });
            });

            document.getElementById('testCustom').addEventListener('click', function() {
                const syntax = document.getElementById('customSyntax').value;
                if (syntax) {
                    runTest(`/api/test-pgvector/syntax?syntax=${encodeURIComponent(syntax)}`);
                }
            });

            // Raw SQL testing
            document.getElementById('testRawSqlButton').addEventListener('click', function() {
                const sql = document.getElementById('rawSqlQuery').value;
                if (sql) {
                    runTest(`/api/test-pgvector/raw-sql?sql=${encodeURIComponent(sql)}`);
                }
            });

            // SQL examples
            document.querySelectorAll('.sql-example').forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const sql = this.textContent;
                    document.getElementById('rawSqlQuery').value = sql;
                });
            });

            // RPC approach test
            document.getElementById('testRpcButton').addEventListener('click', function() {
                runTest('/api/test-pgvector/rpc');
            });

            function runTest(url) {
                loading.classList.remove('hidden');
                results.classList.add('hidden');
                error.classList.add('hidden');

                fetch(url)
                    .then(response => response.json())
                    .then(data => {
                        loading.classList.add('hidden');
                        results.classList.remove('hidden');
                        resultJson.textContent = JSON.stringify(data, null, 2);

                        if (!data.success) {
                            error.classList.remove('hidden');
                            errorJson.textContent = data.error || 'Unknown error';
                        }
                    })
                    .catch(err => {
                        loading.classList.add('hidden');
                        error.classList.remove('hidden');
                        errorJson.textContent = err.message;
                    });
            }
        });
    </script>
</body>
</html>
