' Author: Veck Hsiao 2014/02 @ PLSM, CS, NCCU, Taiwan
' Usage: Count the 3 metrics (Likes, Shares, Comments) by Date of Facebook data set.

Sub post()
    Dim i As Integer
    Dim WriteIndex As Integer
    Dim Likes As Long
    Dim Shares[] As Long
    Dim Comments As Long
    Dim source As String
    Dim target As String
    Dim date_column As Integer

    source = "[table_tobe_counted]"
    target = "[table_of_result]"
    date_column = [date_column]

    WriteIndex = 2  '?g?J [table_of_result]
    
    '?Ĥ@??????
    Likes = Likes + Worksheets(source).Cells(2, 6)
    Shares = Shares + Worksheets(source).Cells(2, 7)
    Comments = Comments + Worksheets(source).Cells(2, 8)
    
    For i = 3 To [final_row]  '?q 3 ?}?l?O?]???? If ?O?P?_?ثe?o?ӻP?e?@?檺?????O?_?ۦP?A?o?˰??O?]???????????ɡA?N?i?H???U??
        If DateValue(Worksheets(source).Cells(i, date_column)) = DateValue(Worksheets(source).Cells(i - 1, date_column)) Then
            Likes = Likes + Worksheets(source).Cells(i, 6)
            Shares = Shares + Worksheets(source).Cells(i, 7)
            Comments = Comments + Worksheets(source).Cells(i, 8)
            If i = [final_row] Then    '?]???̫??@???]???H???A?N?L?k?b???U?@???h?P?_??Else?M???g?J
                Worksheets(target).Cells(WriteIndex, 3) = Likes
                Worksheets(target).Cells(WriteIndex, 4) = Shares
                Worksheets(target).Cells(WriteIndex, 5) = Comments
            End If
        Else
                Worksheets(target).Cells(WriteIndex, 3) = Likes
                Worksheets(target).Cells(WriteIndex, 4) = Shares
                Worksheets(target).Cells(WriteIndex, 5) = Comments
            Likes = Worksheets(source).Cells(i, 6)
            Shares = Worksheets(source).Cells(i, 7)
            Comments = Worksheets(source).Cells(i, 8)
            WriteIndex = WriteIndex + 1
        End If
        
    Next i
End Sub

