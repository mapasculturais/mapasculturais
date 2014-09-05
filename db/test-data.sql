--
-- PostgreSQL database dump
--

SET statement_timeout = 0;
SET lock_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SET check_function_bodies = false;
SET client_min_messages = warning;

SET search_path = public, pg_catalog;

--
-- Data for Name: usr; Type: TABLE DATA; Schema: public; Owner: mapasculturais
--

COPY usr (id, auth_provider, auth_uid, email, last_login_timestamp, create_timestamp, status) FROM stdin;
5	1	1	Staff1@local	2014-05-21 17:41:23	2014-05-21 17:41:23	1
6	1	1	Staff2@local	2014-05-21 17:42:02	2014-05-21 17:42:02	1
7	1	1	Normal1@local	2014-05-21 17:42:35	2014-05-21 17:42:35	1
1	1	1	SuperAdmin1@local	2014-05-21 17:45:03	2014-05-21 17:45:03	1
8	1	1	Normal2@local	2014-05-21 17:42:51	2014-05-21 17:42:51	1
2	1	1	SuperAdmin2@local	2014-05-21 17:38:59	2014-05-21 17:38:59	1
3	1	1	Admin1@local	2014-05-21 17:39:34	2014-05-21 17:39:34	1
4	1	1	Admin2@local	2014-05-21 17:40:15	2014-05-21 17:40:15	1
\.


--
-- Data for Name: agent; Type: TABLE DATA; Schema: public; Owner: mapasculturais
--

COPY agent (id, user_id, type, name, location, _geo_location, short_description, long_description, create_timestamp, status, is_user_profile, is_verified) FROM stdin;
5	5	1	Staff User 1	(0,0)	0101000020E610000000000000000000000000000000000000	short description		2014-05-21 17:57:23	1	t	t
6	6	1	Staff User 2	(0,0)	0101000020E610000000000000000000000000000000000000	short description		2014-05-21 17:57:23	1	t	f
7	7	1	Normal User 1	(0,0)	0101000020E610000000000000000000000000000000000000	short description		2014-05-21 17:57:23	1	t	t
8	8	1	Normal User 2	(0,0)	0101000020E610000000000000000000000000000000000000	short description		2014-05-21 17:57:23	1	t	f
1	1	1	Super Admin 1	(0,0)	0101000020E610000000000000000000000000000000000000	short description		2014-05-21 17:57:23	1	t	t
2	2	1	Super Admin 2	(0,0)	0101000020E610000000000000000000000000000000000000	short description		2014-05-21 17:57:23	1	t	f
3	3	1	Admin 1	(0,0)	0101000020E610000000000000000000000000000000000000	short description		2014-05-21 17:57:23	1	t	t
4	4	1	Admin 1	(0,0)	0101000020E610000000000000000000000000000000000000	short description		2014-05-21 17:57:23	1	t	f
\.


--
-- Name: agent_id_seq; Type: SEQUENCE SET; Schema: public; Owner: mapasculturais
--

SELECT pg_catalog.setval('agent_id_seq', 325, true);


--
-- Data for Name: agent_meta; Type: TABLE DATA; Schema: public; Owner: mapasculturais
--

COPY agent_meta (object_id, key, value) FROM stdin;
\.


--
-- Data for Name: agent_relation; Type: TABLE DATA; Schema: public; Owner: mapasculturais
--

COPY agent_relation (id, agent_id, object_type, object_id, type, has_control, create_timestamp, status) FROM stdin;
\.


--
-- Name: agent_relation_id_seq; Type: SEQUENCE SET; Schema: public; Owner: mapasculturais
--

SELECT pg_catalog.setval('agent_relation_id_seq', 1, true);


--
-- Data for Name: db_update; Type: TABLE DATA; Schema: public; Owner: mapasculturais
--

COPY db_update (name, exec_time) FROM stdin;
0001	2013-12-20 21:39:23.035876
remove agents and spaces with error	2014-01-29 17:06:12.622215
remove agents and spaces with error - 2014-02-07	2014-02-10 19:01:40.686626
create-occurrence_id_seq	2014-04-03 19:41:34.861338
importa programação virada cultural	2014-04-30 18:51:21.983983
importa programação virada cultural - sesc	2014-05-01 01:00:30.210001
importa programação virada cultural - estado	2014-05-02 15:29:28.296556
remove eventos e espacos antigos da virada	2014-05-07 12:47:22.131586
programação virada cultural	2014-05-07 12:48:05.991509
\.


--
-- Data for Name: project; Type: TABLE DATA; Schema: public; Owner: mapasculturais
--

COPY project (id, name, short_description, long_description, public_registration, create_timestamp, status, agent_id, is_verified, type, parent_id, registration_from, registration_to) FROM stdin;
8	Project 8	of Normal User 1	\N	t	2014-05-21 18:04:41	1	8	f	1	\N	\N	\N
1	Project 1	of Super Admin 1	\N	t	2014-05-21 18:04:41	1	1	t	1	\N	\N	\N
2	Project 2	of Super Admin 2	\N	t	2014-05-21 18:04:41	1	2	f	1	\N	\N	\N
3	Project 3	of Admin 1	\N	f	2014-05-21 18:04:41	1	3	t	1	\N	\N	\N
4	Project 4	of Admin 2	\N	f	2014-05-21 18:04:41	1	4	f	1	\N	\N	\N
5	Project 5	of Staff User 1	\N	f	2014-05-21 18:04:41	1	5	t	1	\N	\N	\N
6	Project 6	of Staff User 2	\N	t	2014-05-21 18:04:41	1	6	f	1	\N	\N	\N
7	Project 7	of Normal User 1	\N	f	2014-05-21 18:04:41	1	7	t	1	\N	\N	\N
\.


--
-- Data for Name: event; Type: TABLE DATA; Schema: public; Owner: mapasculturais
--

COPY event (id, project_id, name, short_description, long_description, rules, create_timestamp, status, agent_id, is_verified, type) FROM stdin;
1	\N	Event 1	of Super Admin 1		\N	2014-05-21 18:04:44	1	1	t	1
2	\N	Event 2	of Super Admin 2		\N	2014-05-21 18:04:44	1	2	f	1
3	\N	Event 3	of Admin 1		\N	2014-05-21 18:04:44	1	3	t	1
4	\N	Event 4	of Admin 2		\N	2014-05-21 18:04:44	1	4	f	1
5	\N	Event 5	of Staff User 1		\N	2014-05-21 18:04:44	1	5	t	1
6	\N	Event 6	of Staff User 2		\N	2014-05-21 18:04:44	1	6	f	1
7	\N	Event 7	of Normal User 1		\N	2014-05-21 18:04:44	1	7	t	1
8	\N	Event 8	of Normal User 1		\N	2014-05-21 18:04:44	1	8	f	1
\.


--
-- Name: event_id_seq; Type: SEQUENCE SET; Schema: public; Owner: mapasculturais
--

SELECT pg_catalog.setval('event_id_seq', 495, true);


--
-- Data for Name: event_meta; Type: TABLE DATA; Schema: public; Owner: mapasculturais
--

COPY event_meta (key, object_id, value) FROM stdin;
classificacaoEtaria             	1	Livre
classificacaoEtaria             	2	Livre
classificacaoEtaria             	3	Livre
classificacaoEtaria             	4	Livre
classificacaoEtaria             	5	Livre
classificacaoEtaria             	6	Livre
classificacaoEtaria             	7	Livre
classificacaoEtaria             	8	Livre
\.


--
-- Data for Name: space; Type: TABLE DATA; Schema: public; Owner: mapasculturais
--

COPY space (id, parent_id, location, _geo_location, name, short_description, long_description, create_timestamp, status, type, agent_id, is_verified) FROM stdin;
1	\N	(0,0)	0101000020E610000000000000000000000000000000000000	Space 1	of Super Admin 1		2014-05-21 18:04:38	1	10	1	t
8	\N	(0,0)	0101000020E610000000000000000000000000000000000000	Space 8	of Normal User 1		2014-05-21 18:04:38	1	10	8	f
7	\N	(0,0)	0101000020E610000000000000000000000000000000000000	Space 7	of Normal User 1		2014-05-21 18:04:38	1	10	7	t
6	\N	(0,0)	0101000020E610000000000000000000000000000000000000	Space 6	of Staff User 2		2014-05-21 18:04:38	1	10	6	f
5	\N	(0,0)	0101000020E610000000000000000000000000000000000000	Space 5	of Staff User 1		2014-05-21 18:04:38	1	10	5	t
4	\N	(0,0)	0101000020E610000000000000000000000000000000000000	Space 4	of Admin 2		2014-05-21 18:04:38	1	10	4	f
3	\N	(0,0)	0101000020E610000000000000000000000000000000000000	Space 3	of Admin 1		2014-05-21 18:04:38	1	10	3	t
2	\N	(0,0)	0101000020E610000000000000000000000000000000000000	Space 2	of Super Admin 2		2014-05-21 18:04:38	1	10	2	f
\.


--
-- Data for Name: event_occurrence; Type: TABLE DATA; Schema: public; Owner: mapasculturais
--

COPY event_occurrence (id, space_id, event_id, rule, starts_on, ends_on, starts_at, ends_at, frequency, separation, count, until, timezone_name) FROM stdin;
\.


--
-- Data for Name: event_occurrence_cancellation; Type: TABLE DATA; Schema: public; Owner: mapasculturais
--

COPY event_occurrence_cancellation (id, event_occurrence_id, date) FROM stdin;
\.


--
-- Name: event_occurrence_cancellation_id_seq; Type: SEQUENCE SET; Schema: public; Owner: mapasculturais
--

SELECT pg_catalog.setval('event_occurrence_cancellation_id_seq', 1, true);


--
-- Name: event_occurrence_id_seq; Type: SEQUENCE SET; Schema: public; Owner: mapasculturais
--

SELECT pg_catalog.setval('event_occurrence_id_seq', 141, true);


--
-- Data for Name: event_occurrence_recurrence; Type: TABLE DATA; Schema: public; Owner: mapasculturais
--

COPY event_occurrence_recurrence (id, event_occurrence_id, month, day, week) FROM stdin;
\.


--
-- Name: event_occurrence_recurrence_id_seq; Type: SEQUENCE SET; Schema: public; Owner: mapasculturais
--

SELECT pg_catalog.setval('event_occurrence_recurrence_id_seq', 106, true);


--
-- Data for Name: file; Type: TABLE DATA; Schema: public; Owner: mapasculturais
--

COPY file (id, md5, mime_type, name, object_type, object_id, create_timestamp, grp, description) FROM stdin;
\.


--
-- Name: file_id_seq; Type: SEQUENCE SET; Schema: public; Owner: mapasculturais
--

SELECT pg_catalog.setval('file_id_seq', 1, true);


--
-- Data for Name: metadata; Type: TABLE DATA; Schema: public; Owner: mapasculturais
--

COPY metadata (object_id, object_type, key, value) FROM stdin;
\.


--
-- Data for Name: metalist; Type: TABLE DATA; Schema: public; Owner: mapasculturais
--

COPY metalist (id, object_type, object_id, grp, title, description, value, create_timestamp, "order") FROM stdin;
\.


--
-- Name: metalist_id_seq; Type: SEQUENCE SET; Schema: public; Owner: mapasculturais
--

SELECT pg_catalog.setval('metalist_id_seq', 1, true);


--
-- Data for Name: project_event; Type: TABLE DATA; Schema: public; Owner: mapasculturais
--

COPY project_event (id, event_id, project_id, type, status) FROM stdin;
\.


--
-- Name: project_event_id_seq; Type: SEQUENCE SET; Schema: public; Owner: mapasculturais
--

SELECT pg_catalog.setval('project_event_id_seq', 1, true);


--
-- Name: project_id_seq; Type: SEQUENCE SET; Schema: public; Owner: mapasculturais
--

SELECT pg_catalog.setval('project_id_seq', 320, true);


--
-- Data for Name: project_meta; Type: TABLE DATA; Schema: public; Owner: mapasculturais
--

COPY project_meta (object_id, key, value) FROM stdin;
\.


--
-- Data for Name: role; Type: TABLE DATA; Schema: public; Owner: mapasculturais
--

COPY role (id, usr_id, name) FROM stdin;
1	1	superAdmin
3	3	admin
4	4	admin
5	5	staff
6	6	staff
2	2	superAdmin
\.


--
-- Name: role_id_seq; Type: SEQUENCE SET; Schema: public; Owner: mapasculturais
--

SELECT pg_catalog.setval('role_id_seq', 123, true);


--
-- Name: sp_distrito_gid_seq; Type: SEQUENCE SET; Schema: public; Owner: mapasculturais
--

SELECT pg_catalog.setval('sp_distrito_gid_seq', 96, true);


--
-- Name: sp_regiao_gid_seq; Type: SEQUENCE SET; Schema: public; Owner: mapasculturais
--

SELECT pg_catalog.setval('sp_regiao_gid_seq', 8, true);


--
-- Name: sp_subprefeitura_gid_seq; Type: SEQUENCE SET; Schema: public; Owner: mapasculturais
--

SELECT pg_catalog.setval('sp_subprefeitura_gid_seq', 32, true);


--
-- Name: space_id_seq; Type: SEQUENCE SET; Schema: public; Owner: mapasculturais
--

SELECT pg_catalog.setval('space_id_seq', 461, true);


--
-- Data for Name: space_meta; Type: TABLE DATA; Schema: public; Owner: mapasculturais
--

COPY space_meta (object_id, key, value) FROM stdin;
\.


--
-- Data for Name: term; Type: TABLE DATA; Schema: public; Owner: mapasculturais
--

COPY term (id, taxonomy, term, description) FROM stdin;
2	2	Antropologia	
3	2	Arqueologia	
4	2	Arquitetura-Urbanismo	
5	2	Arquivo	
6	2	Artesanato	
7	2	Artes Visuais	
8	2	Cultura Negra	
9	2	Fotografia	
10	2	Jogos Eletrônicos	
11	2	Circo	
12	2	Filosofia	
\.


--
-- Name: term_id_seq; Type: SEQUENCE SET; Schema: public; Owner: mapasculturais
--

SELECT pg_catalog.setval('term_id_seq', 12, true);


--
-- Data for Name: term_relation; Type: TABLE DATA; Schema: public; Owner: mapasculturais
--

COPY term_relation (term_id, object_type, object_id) FROM stdin;
2	MapasCulturais\\Entities\\Agent	1
3	MapasCulturais\\Entities\\Agent	2
4	MapasCulturais\\Entities\\Agent	3
5	MapasCulturais\\Entities\\Agent	4
6	MapasCulturais\\Entities\\Agent	5
7	MapasCulturais\\Entities\\Agent	6
5	MapasCulturais\\Entities\\Agent	7
4	MapasCulturais\\Entities\\Agent	8
2	MapasCulturais\\Entities\\Space	8
4	MapasCulturais\\Entities\\Space	7
6	MapasCulturais\\Entities\\Space	6
8	MapasCulturais\\Entities\\Space	5
9	MapasCulturais\\Entities\\Space	4
10	MapasCulturais\\Entities\\Space	3
11	MapasCulturais\\Entities\\Space	2
12	MapasCulturais\\Entities\\Space	1
\.


--
-- Name: usr_id_seq; Type: SEQUENCE SET; Schema: public; Owner: mapasculturais
--

SELECT pg_catalog.setval('usr_id_seq', 9, true);


--
-- PostgreSQL database dump complete
--

