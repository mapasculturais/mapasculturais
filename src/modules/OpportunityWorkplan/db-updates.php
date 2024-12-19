<?php

namespace MapasCulturais;

$app = App::i();
$em = $app->em;
$conn = $em->getConnection();

return [
    'create table workplan' => function () {
        __exec("CREATE TABLE registration_workplan (
          id SERIAL PRIMARY KEY,
          agent_id INTEGER,          
          registration_id INTEGER,
          create_timestamp timestamp without time zone NOT NULL,
          update_timestamp timestamp(0) without time zone
      )");

        __exec("ALTER TABLE registration_workplan ADD FOREIGN KEY (registration_id) REFERENCES registration(id) ON DELETE CASCADE");
        __exec("ALTER TABLE registration_workplan ADD FOREIGN KEY (agent_id) REFERENCES agent(id) ON DELETE CASCADE");
    },
    'create table workplan_meta' => function () {
        __exec("CREATE TABLE public.registration_workplan_meta (
            object_id integer NOT NULL,
            key character varying(255) NOT NULL,
            value text,
            id SERIAL NOT NULL
        );");
        __exec("ALTER TABLE registration_workplan_meta ADD FOREIGN KEY (object_id) REFERENCES registration_workplan(id) ON DELETE CASCADE");
    },

    'create table workplan goal' => function () {
        __exec("CREATE TABLE registration_workplan_goal (
            id SERIAL PRIMARY KEY,
            agent_id INTEGER,          
            workplan_id INTEGER,
            create_timestamp timestamp without time zone NOT NULL,
            update_timestamp timestamp(0) without time zone
        )");

        __exec("ALTER TABLE registration_workplan_goal ADD FOREIGN KEY (workplan_id) REFERENCES registration_workplan(id) ON DELETE CASCADE");
        __exec("ALTER TABLE registration_workplan_goal ADD FOREIGN KEY (agent_id) REFERENCES agent(id) ON DELETE CASCADE");
    },
    'create table workplan_goal meta' => function () {
        __exec("CREATE TABLE public.registration_workplan_goal_meta (
              object_id integer NOT NULL,
              key character varying(255) NOT NULL,
              value text,
              id SERIAL NOT NULL
          );");
        __exec("ALTER TABLE registration_workplan_goal_meta ADD FOREIGN KEY (object_id) REFERENCES registration_workplan_goal(id) ON DELETE CASCADE");
    },
    'create table workplan goal delivery' => function () {
        __exec("CREATE TABLE registration_workplan_goal_delivery (
            id SERIAL PRIMARY KEY,
            agent_id INTEGER,     
            goal_id INTEGER,     
            create_timestamp timestamp without time zone NOT NULL,
            update_timestamp timestamp(0) without time zone
        )");

        __exec("ALTER TABLE registration_workplan_goal_delivery ADD FOREIGN KEY (goal_id) REFERENCES registration_workplan_goal(id) ON DELETE CASCADE");
        __exec("ALTER TABLE registration_workplan_goal_delivery ADD FOREIGN KEY (agent_id) REFERENCES agent(id) ON DELETE CASCADE");
    },
    'create table workplan_goal delivery meta' => function () {
        __exec("CREATE TABLE public.registration_workplan_goal_delivery_meta (
              object_id integer NOT NULL,
              key character varying(255) NOT NULL,
              value text,
              id SERIAL NOT NULL
          );");
        __exec("ALTER TABLE registration_workplan_goal_delivery_meta ADD FOREIGN KEY (object_id) REFERENCES registration_workplan_goal_delivery(id) ON DELETE CASCADE");
    },
];
