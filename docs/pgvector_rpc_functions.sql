-- Supabase pgvector RPC Functions
-- This file contains SQL functions to enable vector similarity search in Supabase
-- Execute these in your Supabase SQL editor

-- Basic vector similarity search function for the embeddings table
-- Adjust the vector dimension (1536) to match your embedding model
CREATE OR REPLACE FUNCTION match_embeddings (
  query_embedding vector(1536),
  match_threshold float,
  match_count int,
  source_type text DEFAULT NULL
)
RETURNS TABLE (
  id bigint,
  content text,
  metadata jsonb,
  similarity float
)
LANGUAGE sql STABLE
AS $$
  SELECT
    embeddings.id,
    embeddings.content,
    embeddings.metadata,
    1 - (embeddings.embedding <=> query_embedding) AS similarity
  FROM embeddings
  WHERE 
    (source_type IS NULL OR metadata->>'source_type' = source_type)
    AND 1 - (embeddings.embedding <=> query_embedding) > match_threshold
  ORDER BY embeddings.embedding <=> query_embedding ASC
  LIMIT match_count;
$$;

-- Function to test if pgvector is working correctly
CREATE OR REPLACE FUNCTION test_vector_operation()
RETURNS TABLE (
  operation_name text,
  result float,
  success boolean
)
LANGUAGE plpgsql
AS $$
BEGIN
  RETURN QUERY
  SELECT 
    'cosine_distance' AS operation_name,
    '[1,2,3]'::vector <=> '[4,5,6]'::vector AS result,
    TRUE AS success;
EXCEPTION
  WHEN OTHERS THEN
    RETURN QUERY
    SELECT 
      'cosine_distance' AS operation_name,
      NULL AS result,
      FALSE AS success;
END;
$$;

-- Function to get vector dimensions
CREATE OR REPLACE FUNCTION get_vector_dimensions(
  sample_vector text
)
RETURNS TABLE (
  dimensions integer,
  success boolean
)
LANGUAGE plpgsql
AS $$
BEGIN
  RETURN QUERY
  SELECT 
    vector_dims(sample_vector::vector) AS dimensions,
    TRUE AS success;
EXCEPTION
  WHEN OTHERS THEN
    RETURN QUERY
    SELECT 
      NULL::integer AS dimensions,
      FALSE AS success;
END;
$$;

-- Example usage in Laravel:
-- 
-- $embedding = $this->getEmbedding($query); // Your embedding generation function
-- $embeddingString = '[' . implode(',', $embedding) . ']';
-- 
-- $results = DB::connection('pgsql')->select("
--     SELECT * FROM match_embeddings(
--         '$embeddingString'::vector,
--         0.7,
--         5,
--         'help_document'
--     )
-- ");
