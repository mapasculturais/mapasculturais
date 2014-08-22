--
-- PostgreSQL database dump
--

SET statement_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SET check_function_bodies = false;
SET client_min_messages = warning;

SET search_path = public, pg_catalog;

--
-- Name: agent_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE agent_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: agent; Type: TABLE; Schema: public; Owner: -; Tablespace:
--

CREATE TABLE agent (
    id integer DEFAULT nextval('agent_id_seq'::regclass) NOT NULL,
    user_id integer NOT NULL,
    parent_id integer,
    type smallint NOT NULL,
    name character varying(255) NOT NULL,
    location point,
    _geo_location geography,
    short_description text,
    long_description text,
    create_timestamp timestamp without time zone NOT NULL,
    status smallint NOT NULL,
    is_user_profile boolean DEFAULT false NOT NULL,
    is_verified boolean DEFAULT false NOT NULL
);


--
-- Name: COLUMN agent.location; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN agent.location IS 'type=POINT';


--
-- Name: agent_meta; Type: TABLE; Schema: public; Owner: -; Tablespace:
--

CREATE TABLE agent_meta (
    object_id integer NOT NULL,
    key character(32) NOT NULL,
    value text
);


--
-- Name: agent_relation; Type: TABLE; Schema: public; Owner: -; Tablespace:
--

CREATE TABLE agent_relation (
    id integer NOT NULL,
    agent_id integer NOT NULL,
    object_type character varying(255) NOT NULL,
    object_id integer NOT NULL,
    type character varying(64),
    has_control boolean DEFAULT false NOT NULL,
    create_timestamp timestamp without time zone,
    status smallint
);


--
-- Name: agent_relation_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE agent_relation_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: agent_relation_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE agent_relation_id_seq OWNED BY agent_relation.id;


--
-- Name: authority_request; Type: TABLE; Schema: public; Owner: -; Tablespace:
--

CREATE TABLE authority_request (
    id integer NOT NULL,
    owner_type smallint NOT NULL,
    owner_id integer NOT NULL,
    object_type smallint NOT NULL,
    object_id integer NOT NULL,
    create_timestamp timestamp without time zone NOT NULL,
    status smallint NOT NULL
);


--
-- Name: authority_request_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE authority_request_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: authority_request_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE authority_request_id_seq OWNED BY authority_request.id;


--
-- Name: comment; Type: TABLE; Schema: public; Owner: -; Tablespace:
--

CREATE TABLE comment (
    id integer NOT NULL,
    parent_id integer,
    agent_id integer NOT NULL,
    object_type smallint NOT NULL,
    object_id integer NOT NULL,
    content text NOT NULL,
    create_timestamp timestamp without time zone DEFAULT now() NOT NULL,
    status smallint NOT NULL
);


--
-- Name: contract; Type: TABLE; Schema: public; Owner: -; Tablespace:
--

CREATE TABLE contract (
    id integer NOT NULL,
    object_id integer NOT NULL,
    object_type smallint NOT NULL,
    agent_id integer NOT NULL,
    from_date date,
    to_date date,
    amount numeric NOT NULL,
    is_verified boolean DEFAULT false NOT NULL
);


--
-- Name: contract_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE contract_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: contract_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE contract_id_seq OWNED BY contract.id;


--
-- Name: db_update; Type: TABLE; Schema: public; Owner: -; Tablespace:
--

CREATE TABLE db_update (
    name character varying(255) NOT NULL,
    exec_time timestamp without time zone DEFAULT now() NOT NULL
);


--
-- Name: event; Type: TABLE; Schema: public; Owner: -; Tablespace:
--

CREATE TABLE event (
    id integer NOT NULL,
    project_id integer,
    name character varying(255) NOT NULL,
    short_description text NOT NULL,
    long_description text,
    rules text,
    create_timestamp timestamp without time zone NOT NULL,
    status smallint NOT NULL,
    agent_id integer,
    is_verified boolean DEFAULT false NOT NULL,
    type smallint NOT NULL
);


--
-- Name: event_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE event_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: event_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE event_id_seq OWNED BY event.id;


--
-- Name: event_meta; Type: TABLE; Schema: public; Owner: -; Tablespace:
--

CREATE TABLE event_meta (
    key character(32) NOT NULL,
    object_id integer NOT NULL,
    value text
);


--
-- Name: file_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE file_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: file; Type: TABLE; Schema: public; Owner: -; Tablespace:
--

CREATE TABLE file (
    id integer DEFAULT nextval('file_id_seq'::regclass) NOT NULL,
    md5 character varying(32) NOT NULL,
    mime_type character varying(255) NOT NULL,
    name character varying(255) NOT NULL,
    object_type character varying(255) NOT NULL,
    object_id integer NOT NULL,
    create_timestamp timestamp without time zone DEFAULT now() NOT NULL,
    grp character(32) NOT NULL,
    description character varying(255)
);


--
-- Name: metadata; Type: TABLE; Schema: public; Owner: -; Tablespace:
--

CREATE TABLE metadata (
    object_id integer NOT NULL,
    object_type character varying(255) NOT NULL,
    key character(32) NOT NULL,
    value text
);


--
-- Name: metalist_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE metalist_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: metalist; Type: TABLE; Schema: public; Owner: -; Tablespace:
--

CREATE TABLE metalist (
    id integer DEFAULT nextval('metalist_id_seq'::regclass) NOT NULL,
    object_type character varying(255) NOT NULL,
    object_id integer NOT NULL,
    grp character varying(32) NOT NULL,
    title character varying(255) NOT NULL,
    description text,
    value character varying(2048) NOT NULL,
    create_timestamp timestamp without time zone DEFAULT now() NOT NULL,
    "order" smallint
);


--
-- Name: project; Type: TABLE; Schema: public; Owner: -; Tablespace:
--

CREATE TABLE project (
    id integer NOT NULL,
    name character varying(255) NOT NULL,
    short_description text,
    long_description text,
    public_registration boolean NOT NULL,
    create_timestamp timestamp without time zone NOT NULL,
    status smallint NOT NULL,
    agent_id integer,
    is_verified boolean DEFAULT false NOT NULL,
    type smallint NOT NULL,
    parent_id integer,
    registration_from timestamp without time zone,
    registration_to timestamp without time zone
);


--
-- Name: project_event; Type: TABLE; Schema: public; Owner: -; Tablespace:
--

CREATE TABLE project_event (
    id integer NOT NULL,
    event_id integer NOT NULL,
    project_id integer NOT NULL,
    type smallint NOT NULL,
    status smallint NOT NULL
);


--
-- Name: project_event_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE project_event_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: project_event_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE project_event_id_seq OWNED BY project_event.id;


--
-- Name: project_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE project_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: project_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE project_id_seq OWNED BY project.id;


--
-- Name: project_meta; Type: TABLE; Schema: public; Owner: -; Tablespace:
--

CREATE TABLE project_meta (
    object_id integer NOT NULL,
    key character(32) NOT NULL,
    value text
);


--
-- Name: role; Type: TABLE; Schema: public; Owner: -; Tablespace:
--

CREATE TABLE role (
    id integer NOT NULL,
    usr_id integer NOT NULL,
    name character varying(32) NOT NULL
);


--
-- Name: role_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE role_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: role_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE role_id_seq OWNED BY role.id;


--
-- Name: space; Type: TABLE; Schema: public; Owner: -; Tablespace:
--

CREATE TABLE space (
    id integer NOT NULL,
    parent_id integer,
    location point,
    _geo_location geography,
    name character varying(255) NOT NULL,
    short_description text,
    long_description text,
    create_timestamp timestamp without time zone DEFAULT now() NOT NULL,
    status smallint NOT NULL,
    type smallint NOT NULL,
    agent_id integer,
    public BOOLEAN NOT NULL DEFAULT false,
    is_verified boolean DEFAULT false NOT NULL
);


--
-- Name: COLUMN space.location; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN space.location IS 'type=POINT';


--
-- Name: space_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE space_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: space_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE space_id_seq OWNED BY space.id;


--
-- Name: space_meta; Type: TABLE; Schema: public; Owner: -; Tablespace:
--

CREATE TABLE space_meta (
    object_id integer NOT NULL,
    key character(32) NOT NULL,
    value text
);


--
-- Name: term; Type: TABLE; Schema: public; Owner: -; Tablespace:
--

CREATE TABLE term (
    id integer NOT NULL,
    taxonomy smallint DEFAULT 1 NOT NULL,
    term character varying(255) NOT NULL,
    description text
);


--
-- Name: COLUMN term.taxonomy; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN term.taxonomy IS '1=tag';


--
-- Name: term_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE term_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: term_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE term_id_seq OWNED BY term.id;


--
-- Name: term_relation; Type: TABLE; Schema: public; Owner: -; Tablespace:
--

CREATE TABLE term_relation (
    term_id integer NOT NULL,
    object_type character varying(255) NOT NULL,
    object_id integer NOT NULL
);


--
-- Name: usr_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE usr_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: usr; Type: TABLE; Schema: public; Owner: -; Tablespace:
--

CREATE TABLE usr (
    id integer DEFAULT nextval('usr_id_seq'::regclass) NOT NULL,
    auth_provider smallint NOT NULL,
    auth_uid character varying(512) NOT NULL,
    email character varying(255) NOT NULL,
    last_login_timestamp timestamp without time zone NOT NULL,
    create_timestamp timestamp without time zone DEFAULT now() NOT NULL,
    status smallint NOT NULL
);


--
-- Name: COLUMN usr.auth_provider; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN usr.auth_provider IS '1=openid';


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY agent_relation ALTER COLUMN id SET DEFAULT nextval('agent_relation_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY authority_request ALTER COLUMN id SET DEFAULT nextval('authority_request_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY contract ALTER COLUMN id SET DEFAULT nextval('contract_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY event ALTER COLUMN id SET DEFAULT nextval('event_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY project ALTER COLUMN id SET DEFAULT nextval('project_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY project_event ALTER COLUMN id SET DEFAULT nextval('project_event_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY role ALTER COLUMN id SET DEFAULT nextval('role_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY space ALTER COLUMN id SET DEFAULT nextval('space_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY term ALTER COLUMN id SET DEFAULT nextval('term_id_seq'::regclass);


--
-- Name: agent_meta_pk; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace:
--

ALTER TABLE ONLY agent_meta
    ADD CONSTRAINT agent_meta_pk PRIMARY KEY (object_id, key);


--
-- Name: agent_pk; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace:
--

ALTER TABLE ONLY agent
    ADD CONSTRAINT agent_pk PRIMARY KEY (id);


--
-- Name: agent_relation_pkey; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace:
--

ALTER TABLE ONLY agent_relation
    ADD CONSTRAINT agent_relation_pkey PRIMARY KEY (id);


--
-- Name: authority_request_pk; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace:
--

ALTER TABLE ONLY authority_request
    ADD CONSTRAINT authority_request_pk PRIMARY KEY (id);


--
-- Name: comment_pk; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace:
--

ALTER TABLE ONLY comment
    ADD CONSTRAINT comment_pk PRIMARY KEY (id);


--
-- Name: contract_pk; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace:
--

ALTER TABLE ONLY contract
    ADD CONSTRAINT contract_pk PRIMARY KEY (id);


--
-- Name: db_update_pk; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace:
--

ALTER TABLE ONLY db_update
    ADD CONSTRAINT db_update_pk PRIMARY KEY (name);


--
-- Name: event_meta_pk; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace:
--

ALTER TABLE ONLY event_meta
    ADD CONSTRAINT event_meta_pk PRIMARY KEY (key, object_id);


--
-- Name: event_pk; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace:
--

ALTER TABLE ONLY event
    ADD CONSTRAINT event_pk PRIMARY KEY (id);


--
-- Name: file_pk; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace:
--

ALTER TABLE ONLY file
    ADD CONSTRAINT file_pk PRIMARY KEY (id);


--
-- Name: metadata_pk; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace:
--

ALTER TABLE ONLY metadata
    ADD CONSTRAINT metadata_pk PRIMARY KEY (object_id, object_type, key);


--
-- Name: metalist_pk; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace:
--

ALTER TABLE ONLY metalist
    ADD CONSTRAINT metalist_pk PRIMARY KEY (id);


--
-- Name: project_event_pk; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace:
--

ALTER TABLE ONLY project_event
    ADD CONSTRAINT project_event_pk PRIMARY KEY (id);


--
-- Name: project_meta_pk; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace:
--

ALTER TABLE ONLY project_meta
    ADD CONSTRAINT project_meta_pk PRIMARY KEY (object_id, key);


--
-- Name: project_pk; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace:
--

ALTER TABLE ONLY project
    ADD CONSTRAINT project_pk PRIMARY KEY (id);


--
-- Name: role_pk; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace:
--

ALTER TABLE ONLY role
    ADD CONSTRAINT role_pk PRIMARY KEY (id);


--
-- Name: role_unique; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace:
--

ALTER TABLE ONLY role
    ADD CONSTRAINT role_unique UNIQUE (usr_id, name);


--
-- Name: space_meta_pk; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace:
--

ALTER TABLE ONLY space_meta
    ADD CONSTRAINT space_meta_pk PRIMARY KEY (object_id, key);


--
-- Name: space_pk; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace:
--

ALTER TABLE ONLY space
    ADD CONSTRAINT space_pk PRIMARY KEY (id);


--
-- Name: term_pk; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace:
--

ALTER TABLE ONLY term
    ADD CONSTRAINT term_pk PRIMARY KEY (id);


--
-- Name: term_relation_pk; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace:
--

ALTER TABLE ONLY term_relation
    ADD CONSTRAINT term_relation_pk PRIMARY KEY (term_id, object_type, object_id);


--
-- Name: usr_pk; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace:
--

ALTER TABLE ONLY usr
    ADD CONSTRAINT usr_pk PRIMARY KEY (id);


--
-- Name: agent_relation_all; Type: INDEX; Schema: public; Owner: -; Tablespace:
--

CREATE INDEX agent_relation_all ON agent_relation USING btree (agent_id, object_type, object_id);


--
-- Name: authority_request_idx; Type: INDEX; Schema: public; Owner: -; Tablespace:
--

CREATE INDEX authority_request_idx ON authority_request USING btree (object_type, object_id);


--
-- Name: comment_idx; Type: INDEX; Schema: public; Owner: -; Tablespace:
--

CREATE INDEX comment_idx ON comment USING btree (object_type, object_id);


--
-- Name: contract_idx; Type: INDEX; Schema: public; Owner: -; Tablespace:
--

CREATE INDEX contract_idx ON contract USING btree (object_id, object_type, agent_id);


--
-- Name: space_location; Type: INDEX; Schema: public; Owner: -; Tablespace:
--

CREATE INDEX space_location ON space USING gist (_geo_location);


--
-- Name: space_type; Type: INDEX; Schema: public; Owner: -; Tablespace:
--

CREATE INDEX space_type ON space USING btree (type);


--
-- Name: agent_agent_meta_fk; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY agent_meta
    ADD CONSTRAINT agent_agent_meta_fk FOREIGN KEY (object_id) REFERENCES agent(id);


--
-- Name: agent_contract_fk; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY contract
    ADD CONSTRAINT agent_contract_fk FOREIGN KEY (agent_id) REFERENCES agent(id);


--
-- Name: agent_relation_fk; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY agent_relation
    ADD CONSTRAINT agent_relation_fk FOREIGN KEY (agent_id) REFERENCES agent(id);


--
-- Name: project_project_fk; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY agent
    ADD CONSTRAINT agent_agent_fk FOREIGN KEY (parent_id) REFERENCES agent(id);


--
-- Name: comment_comment_fk; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY comment
    ADD CONSTRAINT comment_comment_fk FOREIGN KEY (parent_id) REFERENCES comment(id);


--
-- Name: event_agent_fk; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY event
    ADD CONSTRAINT event_agent_fk FOREIGN KEY (agent_id) REFERENCES agent(id);
ALTER TABLE ONLY event
    ADD CONSTRAINT project_fk FOREIGN KEY (project_id) REFERENCES project(id);

--
-- Name: event_project_event_fk; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY project_event
    ADD CONSTRAINT event_project_event_fk FOREIGN KEY (event_id) REFERENCES event(id);


--
-- Name: event_project_meta_fk; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY event_meta
    ADD CONSTRAINT event_project_meta_fk FOREIGN KEY (object_id) REFERENCES event(id);


--
-- Name: project_agent_fk; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY project
    ADD CONSTRAINT project_agent_fk FOREIGN KEY (agent_id) REFERENCES agent(id);


--
-- Name: project_project_event_fk; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY project_event
    ADD CONSTRAINT project_project_event_fk FOREIGN KEY (project_id) REFERENCES project(id);


--
-- Name: project_project_fk; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY project
    ADD CONSTRAINT project_project_fk FOREIGN KEY (parent_id) REFERENCES project(id);


--
-- Name: project_project_meta_fk; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY project_meta
    ADD CONSTRAINT project_project_meta_fk FOREIGN KEY (object_id) REFERENCES project(id);


--
-- Name: role_user_fk; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY role
    ADD CONSTRAINT role_user_fk FOREIGN KEY (usr_id) REFERENCES usr(id) ON DELETE CASCADE;


--
-- Name: space_agent_fk; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY space
    ADD CONSTRAINT space_agent_fk FOREIGN KEY (agent_id) REFERENCES agent(id);


--
-- Name: space_space_fk; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY space
    ADD CONSTRAINT space_space_fk FOREIGN KEY (parent_id) REFERENCES space(id);


--
-- Name: space_space_meta_fk; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY space_meta
    ADD CONSTRAINT space_space_meta_fk FOREIGN KEY (object_id) REFERENCES space(id);


--
-- Name: term_term_relation_fk; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY term_relation
    ADD CONSTRAINT term_term_relation_fk FOREIGN KEY (term_id) REFERENCES term(id);


--
-- Name: usr_agent_fk; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY agent
    ADD CONSTRAINT usr_agent_fk FOREIGN KEY (user_id) REFERENCES usr(id);


ALTER TABLE ONLY agent 
    ADD CONSTRAINT agent_agent_fk FOREIGN KEY (parent_id) REFERENCES agent(id);


CREATE DOMAIN frequency AS CHARACTER VARYING CHECK ( VALUE IN ( 'once', 'daily', 'weekly', 'monthly', 'yearly' ) );

CREATE TABLE event_occurrence
(
  id serial PRIMARY KEY,
  space_id integer NOT NULL,
  event_id integer NOT NULL,
  rule text,

  starts_on date,
  ends_on date,
  starts_at timestamp without time zone,
  ends_at timestamp without time zone,
  frequency frequency,
  separation integer not null default 1 constraint positive_separation check (separation > 0),
  count integer,
  "until" date,
  timezone_name text not null default 'Etc/UTC',

  CONSTRAINT event_fk FOREIGN KEY (event_id) REFERENCES event,
  CONSTRAINT space_fk FOREIGN KEY (space_id) REFERENCES space
);

CREATE TABLE event_occurrence_recurrence (
  id serial PRIMARY KEY,
  event_occurrence_id integer,
  "month" integer,
  "day" integer,
  week integer,
  CONSTRAINT event_occurrence_fk FOREIGN KEY (event_occurrence_id) REFERENCES event_occurrence ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS  event_occurrence_cancellation (
  id serial PRIMARY KEY,
  event_occurrence_id integer,
  date date,
  CONSTRAINT event_occurrence_fk FOREIGN KEY (event_occurrence_id) REFERENCES event_occurrence ON DELETE CASCADE
);



-- Event Library

CREATE OR REPLACE FUNCTION  days_in_month(
  check_date DATE
)
  RETURNS INT
  LANGUAGE plpgsql IMMUTABLE
  AS $BODY$
DECLARE
  first_of_month DATE := check_date - ((extract(day from check_date) - 1)||' days')::interval;
BEGIN
  RETURN extract(day from first_of_month + '1 month'::interval - first_of_month);
END;
$BODY$;

CREATE OR REPLACE FUNCTION  generate_recurrences(
  duration INTERVAL,
  original_start_date DATE,
  original_end_date DATE,
  range_start DATE,
  range_end DATE,
  repeat_month INT,
  repeat_week INT,
  repeat_day INT
)
  RETURNS setof DATE
  LANGUAGE plpgsql IMMUTABLE
  AS $BODY$
DECLARE
  start_date DATE := original_start_date;
  next_date DATE;
  intervals INT := FLOOR(intervals_between(original_start_date, range_start, duration));
  current_month INT;
  current_week INT;
BEGIN
  IF repeat_month IS NOT NULL THEN
    start_date := start_date + (((12 + repeat_month - cast(extract(month from start_date) as int)) % 12) || ' months')::interval;
  END IF;
  IF repeat_week IS NULL AND repeat_day IS NOT NULL THEN
    IF duration = '7 days'::interval THEN
      start_date := start_date + (((7 + repeat_day - cast(extract(dow from start_date) as int)) % 7) || ' days')::interval;
    ELSE
      start_date := start_date + (repeat_day - extract(day from start_date) || ' days')::interval;
    END IF;
  END IF;
  LOOP
    next_date := start_date + duration * intervals;
    IF repeat_week IS NOT NULL AND repeat_day IS NOT NULL THEN
      current_month := extract(month from next_date);
      next_date := next_date + (((7 + repeat_day - cast(extract(dow from next_date) as int)) % 7) || ' days')::interval;
      IF extract(month from next_date) != current_month THEN
        next_date := next_date - '7 days'::interval;
      END IF;
      IF repeat_week > 0 THEN
        current_week := CEIL(extract(day from next_date) / 7);
      ELSE
        current_week := -CEIL((1 + days_in_month(next_date) - extract(day from next_date)) / 7);
      END IF;
      next_date := next_date + (repeat_week - current_week) * '7 days'::interval;
    END IF;
    EXIT WHEN next_date > range_end;

    IF next_date >= range_start AND next_date >= original_start_date THEN
      RETURN NEXT next_date;
    END IF;

    if original_end_date IS NOT NULL AND range_start >= original_start_date + (duration*intervals) AND range_start <= original_end_date + (duration*intervals) THEN
      RETURN NEXT next_date;
    END IF;
    intervals := intervals + 1;
  END LOOP;
END;
$BODY$;

CREATE OR REPLACE FUNCTION  interval_for(
  recurs frequency
)
  RETURNS INTERVAL
  LANGUAGE plpgsql IMMUTABLE
  AS $BODY$
BEGIN
  IF recurs = 'daily' THEN
    RETURN '1 day'::interval;
  ELSIF recurs = 'weekly' THEN
    RETURN '7 days'::interval;
  ELSIF recurs = 'monthly' THEN
    RETURN '1 month'::interval;
  ELSIF recurs = 'yearly' THEN
    RETURN '1 year'::interval;
  ELSE
    RAISE EXCEPTION 'Recurrence % not supported by generate_recurrences()', recurs;
  END IF;
END;
$BODY$;

CREATE OR REPLACE FUNCTION  intervals_between(
  start_date DATE,
  end_date DATE,
  duration INTERVAL
)
  RETURNS FLOAT
  LANGUAGE plpgsql IMMUTABLE
  AS $BODY$
DECLARE
  count FLOAT := 0;
  multiplier INT := 512;
BEGIN
  IF start_date > end_date THEN
    RETURN 0;
  END IF;
  LOOP
    WHILE start_date + (count + multiplier) * duration < end_date LOOP
      count := count + multiplier;
    END LOOP;
    EXIT WHEN multiplier = 1;
    multiplier := multiplier / 2;
  END LOOP;
  count := count + (extract(epoch from end_date) - extract(epoch from (start_date + count * duration))) / (extract(epoch from end_date + duration) - extract(epoch from end_date))::int;
  RETURN count;
END
$BODY$;

CREATE OR REPLACE FUNCTION recurrences_for(
  event event_occurrence,
  range_start TIMESTAMP,
  range_end  TIMESTAMP
)
  RETURNS SETOF DATE
  LANGUAGE plpgsql STABLE
  AS $BODY$
DECLARE
  recurrence event_occurrence_recurrence;
  recurrences_start DATE := COALESCE(event.starts_at::date, event.starts_on);
  recurrences_end DATE := range_end;
  duration INTERVAL := interval_for(event.frequency) * event.separation;
  next_date DATE;
BEGIN
  IF event.until IS NOT NULL AND event.until < recurrences_end THEN
    recurrences_end := event.until;
  END IF;
  IF event.count IS NOT NULL AND recurrences_start + (event.count - 1) * duration < recurrences_end THEN
    recurrences_end := recurrences_start + (event.count - 1) * duration;
  END IF;

  FOR recurrence IN
    SELECT event_occurrence_recurrence.*
      FROM (SELECT NULL) AS foo
      LEFT JOIN event_occurrence_recurrence
        ON event_occurrence_id = event.id
  LOOP
    FOR next_date IN
      SELECT *
        FROM generate_recurrences(
          duration,
          recurrences_start,
          COALESCE(event.ends_at::date, event.ends_on),
          range_start::date,
          recurrences_end,
          recurrence.month,
          recurrence.week,
          recurrence.day
        )
    LOOP
      RETURN NEXT next_date;
    END LOOP;
  END LOOP;
  RETURN;
END;
$BODY$;

CREATE OR REPLACE FUNCTION recurring_event_occurrence_for(
  range_start TIMESTAMP,
  range_end  TIMESTAMP,
  time_zone CHARACTER VARYING,
  event_occurrence_limit INT
)
  RETURNS SETOF event_occurrence
  LANGUAGE plpgsql STABLE
  AS $BODY$
DECLARE
  event event_occurrence;
  original_date DATE;
  original_date_in_zone DATE;
  start_time TIME;
  start_time_in_zone TIME;
  next_date DATE;
  next_time_in_zone TIME;
  duration INTERVAL;
  time_offset INTERVAL;
  recurrences_start DATE := CASE WHEN (timezone('UTC', range_start) AT TIME ZONE time_zone) < range_start THEN (timezone('UTC', range_start) AT TIME ZONE time_zone)::date ELSE range_start END;
  recurrences_end DATE := CASE WHEN (timezone('UTC', range_end) AT TIME ZONE time_zone) > range_end THEN (timezone('UTC', range_end) AT TIME ZONE time_zone)::date ELSE range_end END;
BEGIN
  FOR event IN
    SELECT *
      FROM event_occurrence
      WHERE
        frequency <> 'once' OR
        (frequency = 'once' AND
          ((starts_on IS NOT NULL AND ends_on IS NOT NULL AND starts_on <= (timezone('UTC', range_end) AT TIME ZONE time_zone)::date AND ends_on >= (timezone('UTC', range_start) AT TIME ZONE time_zone)::date) OR
           (starts_on IS NOT NULL AND starts_on <= (timezone('UTC', range_end) AT TIME ZONE time_zone)::date AND starts_on >= (timezone('UTC', range_start) AT TIME ZONE time_zone)::date) OR
           (starts_at <= range_end AND ends_at >= range_start)))
  LOOP
    IF event.frequency = 'once' THEN
      RETURN NEXT event;
      CONTINUE;
    END IF;

    -- All-day event
    IF event.starts_on IS NOT NULL AND event.ends_on IS NULL THEN
      original_date := event.starts_on;
      duration := '1 day'::interval;
    -- Multi-day event
    ELSIF event.starts_on IS NOT NULL AND event.ends_on IS NOT NULL THEN
      original_date := event.starts_on;
      duration := timezone(time_zone, event.ends_on) - timezone(time_zone, event.starts_on);
    -- Timespan event
    ELSE
      original_date := event.starts_at::date;
      original_date_in_zone := (timezone('UTC', event.starts_at) AT TIME ZONE event.timezone_name)::date;
      start_time := event.starts_at::time;
      start_time_in_zone := (timezone('UTC', event.starts_at) AT time ZONE event.timezone_name)::time;
      duration := event.ends_at - event.starts_at;
    END IF;

    IF event.count IS NOT NULL THEN
      recurrences_start := original_date;
    END IF;

    FOR next_date IN
      SELECT occurrence
        FROM (
          SELECT * FROM recurrences_for(event, recurrences_start, recurrences_end) AS occurrence
          UNION SELECT original_date
          LIMIT event.count
        ) AS occurrences
        WHERE
          occurrence::date <= recurrences_end AND
          (occurrence + duration)::date >= recurrences_start AND
          occurrence NOT IN (SELECT date FROM event_occurrence_cancellation WHERE event_occurrence_id = event.id)
        LIMIT event_occurrence_limit
    LOOP
      -- All-day event
      IF event.starts_on IS NOT NULL AND event.ends_on IS NULL THEN
        CONTINUE WHEN next_date < (timezone('UTC', range_start) AT TIME ZONE time_zone)::date OR next_date > (timezone('UTC', range_end) AT TIME ZONE time_zone)::date;
        event.starts_on := next_date;

      -- Multi-day event
      ELSIF event.starts_on IS NOT NULL AND event.ends_on IS NOT NULL THEN
        event.starts_on := next_date;
        CONTINUE WHEN event.starts_on > (timezone('UTC', range_end) AT TIME ZONE time_zone)::date;
        event.ends_on := next_date + duration;
        CONTINUE WHEN event.ends_on < (timezone('UTC', range_start) AT TIME ZONE time_zone)::date;

      -- Timespan event
      ELSE
        next_time_in_zone := (timezone('UTC', (next_date + start_time)) at time zone event.timezone_name)::time;
        time_offset := (original_date_in_zone + next_time_in_zone) - (original_date_in_zone + start_time_in_zone);
        event.starts_at := next_date + start_time - time_offset;

        CONTINUE WHEN event.starts_at > range_end;
        event.ends_at := event.starts_at + duration;
        CONTINUE WHEN event.ends_at < range_start;
      END IF;

      RETURN NEXT event;
    END LOOP;
  END LOOP;
  RETURN;
END;
$BODY$;
