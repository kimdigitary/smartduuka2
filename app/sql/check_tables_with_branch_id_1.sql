CREATE
TEMP TABLE branch_id_matches (
                                        table_name text,
                                        row_count bigint
);

DO
$$
    DECLARE
r RECORD;
        cnt
BIGINT;
BEGIN
FOR r IN
SELECT table_schema, table_name
FROM information_schema.columns
WHERE column_name = 'branch_id'
  AND table_schema NOT IN ('pg_catalog', 'information_schema') LOOP
                EXECUTE format(
                        'SELECT COUNT(*) FROM %I.%I WHERE branch_id = 1',
                        r.table_schema,
                        r.table_name
                        )
INTO cnt;

IF
cnt > 0 THEN
                    INSERT INTO branch_id_matches
                    VALUES (r.table_schema || '.' || r.table_name, cnt);
END IF;
END LOOP;
END $$;

SELECT *
FROM branch_id_matches
ORDER BY row_count DESC;
