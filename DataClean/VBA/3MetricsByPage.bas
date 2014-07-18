' Author: Veck Hsiao 2014/02 @ PLSM, CS, NCCU, Taiwan
' Usage: Count the 3 metrics (Likes, Shares, Comments) by Page of Facebook data set.

Sub test()
    Dim likeSum As Long     '�� Integer �|���� (89802 �N�|����)
    Dim commentSum As Long
    Dim shareSum As Long
    Dim j As Integer    'for sheet �������o���
    Dim source As String
    Dim target As String
    Dim page_column As Integer

    source = "[table_tobe_counted]"
    target = "[table_of_result]"

    page_column = [column_of_page_name]
    
    j = 2               'From 2 �]�� 1 �O column title
    
    For i = 1 To [final_row]     '�o�� From 1 ���F�n�b�Ĥ@�������� Else �� sum ���Ĥ@�� Page Name ���Ĥ@�� ���g��
    
        If Worksheets(source).Cells(i, page_column) = Worksheets(source).Cells(i + 1, page_column) Then
            likeSum = likeSum + Worksheets(source).Cells(i + 1, 6)         '�έp���g��
            commentSum = commentSum + Worksheets(source).Cells(i + 1, 7)   '�έp�^����
            shareSum = shareSum + Worksheets(source).Cells(i + 1, 8)       '�έp���ɼ�
            
        Else    '���U�@�� Page Name
            If Not i = 1 Then
               Worksheets(target).Cells(j, 1) = Worksheets(source).Cells(i, page_column)    '�g�JPage Name
               Worksheets(target).Cells(j, 3) = likeSum    '�g�J���g��
               Worksheets(target).Cells(j, 4) = commentSum '�g�J�^����
               Worksheets(target).Cells(j, 5) = shareSum   '�g�J���ɼ�
               j = j + 1
            End If
            
            '�����s Page Name ���Ĥ@��
            likeSum = Worksheets(source).Cells(i + 1, 6)
            commentSum = Worksheets(source).Cells(i + 1, 7)
            shareSum = Worksheets(source).Cells(i + 1, 8)
        End If
    Next i
End Sub
