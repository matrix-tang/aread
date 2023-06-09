create table mtm
(
    id          int auto_increment
        primary key,
    stock_plate varchar(255)            not null comment '股票板块、分类',
    stock_score decimal(20, 2)          not null comment '动量分值',
    insert_date varchar(255)            not null comment '插入日期 20230523',
    bak         varchar(255) default '' null comment '备注'
)
    comment '动量模型' charset = utf8mb4;

create table stock
(
    id               int auto_increment
        primary key,
    stock_id         varchar(255)                not null comment '股票编号',
    stock_name       varchar(255)                not null comment '股票中文名',
    stock_plate      varchar(255)                not null comment '股票板块、分类',
    stock_plate_desc varchar(255)                not null comment '股票分类（全）',
    stock_type       tinyint        default 1    null comment '插入用途 1、动量模型 2、一年新高',
    `change`         decimal(20, 2) default 0.00 not null comment '涨跌幅',
    insert_date      varchar(255)                not null comment '插入日期 20230523',
    bak              varchar(255)   default ''   null comment '备注'
)
    comment '动量模型股票信息' charset = utf8mb4;

