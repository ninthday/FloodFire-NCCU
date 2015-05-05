ALTER TABLE article add ts TIMESTAMP;
UPDATE article set ts = str_to_date(time, "%a %b %e %H:%i:%s %Y");

ALTER TABLE comment add ts TIMESTAMP;
UPDATE comment set ts = str_to_date(concat("2014 ", time), "%Y %m/%d %H:%i");

ALTER table article drop `time`;
ALTER table comment drop `time`;