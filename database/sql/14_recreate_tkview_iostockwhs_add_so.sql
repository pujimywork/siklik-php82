CREATE OR REPLACE FORCE VIEW TKVIEW_IOSTOCKWHS
    ("PRODUCT_ID", "TXN_STATUS", "QTY_D", "QTY_K", "TXN_DATE", "TXN_NO", "PRODUCT_NAME") AS
(
    SELECT b.product_id, 'RCV', SUM(qty), 0, rcv_date, a.rcv_no, product_name
    FROM TKTXN_RCVHDRS a, TKTXN_RCVDTLS b, TKMST_PRODUCTS c
    WHERE a.rcv_no = b.rcv_no
        AND rcv_status NOT IN ('A','F')
        AND b.product_id = c.product_id
    GROUP BY b.product_id, 'RCV', 0, rcv_date, a.rcv_no, product_name
    UNION ALL
    SELECT b.product_id, 'SLS', 0, SUM(qty), sls_date, a.sls_no, product_name
    FROM TKTXN_SLSDTLS b, TKTXN_SLSHDRS a, TKMST_PRODUCTS c
    WHERE a.sls_no = b.sls_no
        AND b.product_id = c.product_id
        AND sls_status NOT IN ('A','F')
    GROUP BY b.product_id, 'SLS', 0, sls_date, a.sls_no, product_name
    UNION ALL
    SELECT b.product_id, 'RJ', 0, SUM(qty), rj_date, a.rj_no, product_name
    FROM RSTXN_RJOBATS b, RSTXN_RJHDRS a, TKMST_PRODUCTS c
    WHERE a.rj_no = b.rj_no
        AND b.product_id = c.product_id
        AND rj_status NOT IN ('A','F')
    GROUP BY b.product_id, 'RJ', 0, rj_date, a.rj_no, product_name
    UNION ALL
    SELECT s.product_id, 'SO', NVL(s.so_d, 0), NVL(s.so_k, 0),
           s.so_date, s.so_no, c.product_name
    FROM TKTXN_SOWHS s, TKMST_PRODUCTS c
    WHERE s.product_id = c.product_id
)
/
