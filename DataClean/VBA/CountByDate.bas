' Author: Veck Hsiao 2014/02 @ PLSM, CS, NCCU, Taiwan
' Usage: Count the number of entry by date

Sub post()
    Dim i As Integer
    Dim WriteIndex As Integer
    Dim sum As Integer
    Dim source As String
    Dim target As String
    Dim date_column As Integer

    source = "[table_tobe_counted]"
    target = "[table_of_result]"
    date_column = [column_of_date]

    WriteIndex = 2  '�g�J �o��Ʈɶ��ǦC
    sum = 1         'sum �q 1 �}�l�O�]���ڭ̬O�q�C�Ӥ�����ĤG���}�l�p��֥[�A�p�G sum �@�}�l�O 0 �N�|�ֺ�Ĥ@��
    
    For i = 3 To [final_row]  '�q 3 �}�l�O�]���� If �O�P�_�ثe�o�ӻP�e�@�檺����O�_�ۦP�A�o�˰��O�]��������ɡA�N�i�H���U��
        If DateValue(Worksheets(source).Cells(i, date_column)) = DateValue(Worksheets(source).Cells(i - 1, date_column)) Then
            sum = sum + 1
            If i = [final_row] Then    '�]���̫�@���]���H��A�N�L�k�b��U�@���h�P�_��Else�M��g�J
                Worksheets(target).Cells(WriteIndex, 3) = sum
            End If
        Else
            Worksheets(target).Cells(WriteIndex, 3) = sum  '�g�J�έp���G
            sum = 1
            WriteIndex = WriteIndex + 1
        End If
        
    Next i
End Sub

'NOTE: �]�� Excel 2013 �ɶ��Ƨǥu���ܥ� A-Z ���覡�A�ܦ��|�O�q�̰����Ħ줸�}�l����ƱƧ�
'�ɭP�p 2013/5/10 �|�Ʀb 2013/5/2 ���e�� (�]�� 1 < 2)

