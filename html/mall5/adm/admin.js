function check_all(f, fld)
{
	// �輱�� 201211 :
	if(fld)
	    var chk = document.getElementsByName(fld);
	else
	    var chk = document.getElementsByName("chk[]");

    for (i=0; i<chk.length; i++)
        chk[i].checked = f.chkall.checked;
}

function btn_check(f, act)
{
    if (act == "update") // ���ü���
    {
        f.action = list_update_php;
        str = "����";
    }
    else if (act == "delete") // ���û���
    {
        f.action = list_delete_php;
        str = "����";
    }
    else
        return;

    var chk = document.getElementsByName("chk[]");
    var bchk = false;

    for (i=0; i<chk.length; i++)
    {
        if (chk[i].checked)
            bchk = true;
    }

    if (!bchk)
    {
        alert(str + "�� �ڷḦ �ϳ� �̻� �����ϼ���.");
        return;
    }

    if (act == "delete")
    {
        if (!confirm("������ �ڷḦ ���� ���� �Ͻðڽ��ϱ�?"))
            return;
    }

    f.submit();
}
