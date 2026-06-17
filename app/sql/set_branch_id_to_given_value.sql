DO $$
DECLARE
r RECORD;
BEGIN
FOR r IN
SELECT table_schema, table_name
FROM information_schema.columns
WHERE column_name = 'branch_id'
  AND table_schema NOT IN ('pg_catalog', 'information_schema')
    LOOP
        EXECUTE format(
            'UPDATE %I.%I SET branch_id = 14',
            r.table_schema,
            r.table_name
        );

RAISE NOTICE 'Updated %.%', r.table_schema, r.table_name;
END LOOP;
END $$;
