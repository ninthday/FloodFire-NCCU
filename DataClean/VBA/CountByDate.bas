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

    WriteIndex = 2  '寫入 發文數時間序列
    sum = 1         'sum 從 1 開始是因為我們是從每個日期的第二筆開始計算累加，如果 sum 一開始是 0 就會少算第一筆
    
    For i = 3 To [final_row]  '從 3 開始是因為我 If 是判斷目前這個與前一格的日期是否相同，這樣做是因為當跨日期時，就可以停下來
        If DateValue(Worksheets(source).Cells(i, date_column)) = DateValue(Worksheets(source).Cells(i - 1, date_column)) Then
            sum = sum + 1
            If i = [final_row] Then    '因為最後一筆跑完以後，就無法在到下一筆去判斷為Else然後寫入
                Worksheets(target).Cells(WriteIndex, 3) = sum
            End If
        Else
            Worksheets(target).Cells(WriteIndex, 3) = sum  '寫入統計結果
            sum = 1
            WriteIndex = WriteIndex + 1
        End If
        
    Next i
End Sub

'NOTE: 因為 Excel 2013 時間排序只能選擇用 A-Z 的方式，變成會是從最高有效位元開始的基數排序
'導致如 2013/5/10 會排在 2013/5/2 的前面 (因為 1 < 2)

