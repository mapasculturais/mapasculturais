ALTER TABLE event ADD project_id INTEGER;
ALTER TABLE event ADD CONSTRAINT project_fk FOREIGN KEY (project_id) REFERENCES project(id);