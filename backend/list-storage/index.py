import json
import os
import boto3

def handler(event: dict, context) -> dict:
    '''Список файлов из S3-хранилища проекта (папка bluditCMS)'''
    if event.get('httpMethod') == 'OPTIONS':
        return {'statusCode': 200, 'headers': {'Access-Control-Allow-Origin': '*', 'Access-Control-Allow-Methods': 'GET, OPTIONS', 'Access-Control-Allow-Headers': 'Content-Type'}, 'body': ''}

    params = event.get('queryStringParameters') or {}
    prefix = params.get('prefix', 'bluditCMS/')
    file_key = params.get('file')

    s3 = boto3.client(
        's3',
        endpoint_url='https://bucket.poehali.dev',
        aws_access_key_id=os.environ['AWS_ACCESS_KEY_ID'],
        aws_secret_access_key=os.environ['AWS_SECRET_ACCESS_KEY']
    )

    if file_key:
        obj = s3.get_object(Bucket='files', Key=file_key)
        content = obj['Body'].read().decode('utf-8', errors='replace')
        return {
            'statusCode': 200,
            'headers': {'Access-Control-Allow-Origin': '*', 'Content-Type': 'application/json'},
            'body': json.dumps({'key': file_key, 'content': content[:50000]})
        }

    resp = s3.list_objects_v2(Bucket='files', Prefix=prefix, MaxKeys=500)
    files = [{'key': o['Key'], 'size': o['Size']} for o in resp.get('Contents', [])]

    return {
        'statusCode': 200,
        'headers': {'Access-Control-Allow-Origin': '*', 'Content-Type': 'application/json'},
        'body': json.dumps({'count': len(files), 'files': files})
    }
