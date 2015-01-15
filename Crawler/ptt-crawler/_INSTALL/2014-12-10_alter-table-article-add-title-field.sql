ALTER TABLE article add column `title` varchar(50) not null default '' after `nick`;

update article
inner join list
on article.id = list.id
set article.title = list.title;
