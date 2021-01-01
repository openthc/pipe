--
-- Schema for OpenTHC Pipe
--

CREATE USER openthc_pipe_root WITH ENCRYPTED PASSWORD 'openthc_pipe_root';
CREATE USER openthc_pipe WITH ENCRYPTED PASSWORD 'openthc_pipe';

CREATE DATABASE openthc_pipe WITH OWNER openthc_pipe_root;

\c openthc_pipe

CREATE TABLE log_audit (
	id character varying(26) not null primary key,
	lic_hash character varying(32) not null,
	req_time timestamp with time zone not null default now(),
	res_time timestamp with time zone,
	req_head text,
	req_body text,
	res_info jsonb,
	res_head text,
	res_body text
);

ALTER TABLE log_audit owner TO openthc_pipe_root;

GRANT SELECT,INSERT,UPDATE ON log_audit TO openthc_pipe;
