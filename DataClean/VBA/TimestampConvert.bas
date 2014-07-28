' 將 Mon Jan 01 00:00:00 +0000 1900 轉換成 1900/01/01 00:00:00 PM/AM 格式，以便依照日期計數
' [ parse(5)/parse(1)/parse(2) parse(3) ]

Sub ToTaiwan()
    Dim parse() As String
    Dim i As Integer
    Dim sheet As String
    Dim date_column As Integer

    sheet = "[sheet_tobe_stranslate]"
    date_column = [column_of_date]
    
    For i = [first_data_row] To [final_row]
    
        parse = Split(Sheets(sheet).Cells(i, date_column))
      
        
        Dim formatted As String
        Dim month As String
        
        'You could customize your month cases
        Select Case parse(1)    'switch case
            Case "Jun"
                month = "6"
            ' more Case = { "Jan", "Feb", "Mar", "Apr", "May", "Jun",
            ' "Jul", "Aug", "Sep", "Oct", "Nov", "Dec" }
        End Select
        
        'Optional timestamp format, uncomment for your need
        formatted = parse(5) + "/" + month + "/" + parse(2) + " " + parse(3)
        'formatted2 = parse(5) + "-" + month + "-" + parse(2) + " " + parse(3)

        'Debug.Print formatted
        
        Dim NewTimestamp As Date
        NewTimestamp = formatted
        
        Sheets(sheet).Cells(i, date_column) = NewTimestamp
        
     Next i
End Sub
