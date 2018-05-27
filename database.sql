-- Passageiro(cod, id, nome, sobrenome, data_nascimento)
-- Assento(id_passageiro, id_voo, num_assento)
-- Voo(id, numero, data_partida, data_chegada, hora_partida, hora_chegada, qtd_passageiro, id_aeroporto_origem, id_aeroporto_destino, id_linha_aerea)
-- Linha_Aerea(id, nome)
-- Aeroporto(id, sigla, nome, id_cidade)
-- Cidade(id, nome, id_pais)
-- Pais(id, nome)

create table if not exists passageiro (
	id int primary key auto_increment,
	nome varchar(255),
	data_nascimento date
);

create table if not exists assento (
	id int primary key auto_increment,
	voo_id int,
	passageiro_id int,
	num_assento int
);

create table if not exists voo (
	id int primary key auto_increment,
	partida timestamp,
	chegada timestamp,
	qtd_passageiro int,
	aeroporto_origem_id int,
	aeroporto_destino_id int
);

create table if not exists aeroporto (
	id int primary key auto_increment,
	sigla char(3),
	nome varchar(255),
	pais varchar(255),
	cidade varchar(255)
);