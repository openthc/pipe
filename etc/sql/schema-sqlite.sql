--
-- Sqlite Schema for OpenTHC Pipe
--

CREATE TABLE log_audit (
	id text not null primary key,
	license_id text,
	req_time timestamp with time zone not null default CURRENT_TIMESTAMP,
	res_time timestamp with time zone,
	req_name text,
	req_head text,
	req_body text,
	res_head text,
	res_body text,
	res_meta jsonb
);
